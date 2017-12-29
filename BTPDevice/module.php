<?php
class BTPDevice extends IPSModule {

  public function Create() {
    parent::Create();
    $this->RegisterPropertyString('Mac', '');
    $this->RegisterPropertyInteger('ScanInterval', 30);
    $this->RegisterPropertyBoolean('BluetoothLE', false);

    $this->RegisterTimer('Update', 0, 'BTP_Scan($_IPS[\'TARGET\'], 0);');

    if($oldInterval = @$this->GetIDForIdent('INTERVAL')) IPS_DeleteEvent($oldInterval);
  }

  public function ApplyChanges() {
    parent::ApplyChanges();

    $stateId = $this->RegisterVariableBoolean('STATE', 'Zustand', '~Presence', 1);
    $presentId = $this->RegisterVariableInteger('PRESENT_SINCE', 'Anwesend seit', '~UnixTimestamp', 3);
    $absentId = $this->RegisterVariableInteger('ABSENT_SINCE', 'Abwesend seit', '~UnixTimestamp', 3);
    if ($this->ReadPropertyBoolean('BluetoothLE')) {
      if($nameId = @$this->GetIDForIdent('NAME')) IPS_DeleteVariable($nameId);
    } else {
      $nameId = $this->RegisterVariableString('NAME', 'Name', '', 2);
    }

    IPS_SetIcon($this->GetIDForIdent('STATE'), 'Motion');
    if($nameId = @$this->GetIDForIdent('NAME')) IPS_SetIcon($nameId, 'Keyboard');
    IPS_SetIcon($this->GetIDForIdent('PRESENT_SINCE'), 'Clock');
    IPS_SetIcon($this->GetIDForIdent('ABSENT_SINCE'), 'Clock');

    $this->SetTimerInterval('Update', $this->ReadPropertyInteger('ScanInterval') * 1000);
  }

  /*
   * Sucht nach dem Bluetoothdevice
   */
  public function Scan() {
    if(IPS_SemaphoreEnter('BTPScan', 6000)) {
      $mac = strtoupper($this->ReadPropertyString('Mac'));
      if (preg_match('/^(?:[0-9A-F]{2}[:]?){6}$/i', $mac)) {
        $lastState = GetValueBoolean($this->GetIDForIdent('STATE'));
        if ($this->ReadPropertyBoolean('BluetoothLE')) {
          $timeout = time() + 5;
          $handle = popen("stdbuf -oL hcitool -i hci0 lescan", "r");
          stream_set_blocking($handle, false);
          $output = '';
          $state = false;
          do {
            $output .= fread($handle, 1024);
            if (strstr($output, $mac)) {
              $state = true;
              break;
            }
            usleep(250000);
          } while (time() < $timeout);
          shell_exec("pkill --signal SIGINT hcitool");
          pclose($handle);
        } else {
          $search = trim(shell_exec("hcitool -i hci0 name $mac"));
          $state = ($search != '');
          if ($state) SetValueString($this->GetIDForIdent('NAME'), $search);
        }
        SetValueBoolean($this->GetIDForIdent('STATE'), $state);

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
