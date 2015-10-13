<?
class BTPDevice extends IPSModule {
  public function Create() {
    parent::Create();
    $this->RegisterPropertyString('Mac', '');
    $this->RegisterPropertyInteger('ScanInterval', 60);
  }

  public function ApplyChanges() {
    parent::ApplyChanges();

    $this->RegisterPropertyInteger('ScanInterval', 30);
    $stateId = $this->RegisterVariableBoolean('STATE', 'Zustand', '~Presence', 1);
    $presentId = $this->RegisterVariableInteger('PRESENT_SINCE', 'Anwesend seit', '~UnixTimestamp', 3);
    $absentId = $this->RegisterVariableInteger('ABSENT_SINCE', 'Abwesend seit', '~UnixTimestamp', 3);
    $nameId = $this->RegisterVariableString('NAME', 'Name', '', 2);

    IPS_SetIcon($this->GetIDForIdent('STATE'), 'Motion');
    IPS_SetIcon($this->GetIDForIdent('NAME'), 'Keyboard');
    IPS_SetIcon($this->GetIDForIdent('PRESENT_SINCE'), 'Clock');
    IPS_SetIcon($this->GetIDForIdent('ABSENT_SINCE'), 'Clock');

    $this->RegisterTimer('INTERVAL', $this->ReadPropertyInteger('ScanInterval'), 'BTP_Scan($id)');
  }

  protected function RegisterTimer($ident, $interval, $script) {
    $id = @IPS_GetObjectIDByIdent($ident, $this->InstanceID);

    if ($id && IPS_GetEvent($id)['EventType'] <> 1) {
      IPS_DeleteEvent($id);
      $id = 0;
    }

    if (!$id) {
      $id = IPS_CreateEvent(1);
      IPS_SetParent($id, $this->InstanceID);
      IPS_SetIdent($id, $ident);
    }

    IPS_SetName($id, $ident);
    IPS_SetHidden($id, true);
    IPS_SetEventScript($id, "\$id = \$_IPS['TARGET'];\n$script;");

    if (!IPS_EventExists($id)) throw new Exception("Ident with name $ident is used for wrong object type");

    if (!($interval > 0)) {
      IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);
      IPS_SetEventActive($id, false);
    } else {
      IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $interval);
      IPS_SetEventActive($id, true);
    }
  }

  public function Scan() {
    if(IPS_SemaphoreEnter('BTPScan', 5000)) {
      $mac = $this->ReadPropertyString('Mac');
      if (preg_match('/^(?:[0-9A-F]{2}[:]?){6}$/i', $mac)) {
        $lastState = GetValueBoolean($this->GetIDForIdent('STATE'));
        $search = trim(shell_exec("sudo hcitool name $mac"));
        $state = ($search != '');
        SetValueBoolean($this->GetIDForIdent('STATE'), $state);

        if ($state) SetValueString($this->GetIDForIdent('NAME'), $search);
        if ($lastState != $state) {
          if ($state) SetValueInteger($this->GetIDForIdent('PRESENT_SINCE'), time());
          if (!$state) SetValueInteger($this->GetIDForIdent('ABSENT_SINCE'), time());
        }

        IPS_SetHidden($this->GetIDForIdent('PRESENT_SINCE'), !$state);
        IPS_SetHidden($this->GetIDForIdent('ABSENT_SINCE'), $state);
      }
      IPS_SemaphoreLeave('BTPScan');
    } else {
      IPS_LogMessage('BTPDevice', 'Semaphore Timeout');
    }
  }

}
?>
