<?php


declare(strict_types=1);

trait TSW_ScheduleAction
{
    /**
     * Shows the actual action of the scheduled action
     *
     * @return void
     * @throws Exception
     */
    public function ShowActualScheduleAction(): void
    {
        $warning = json_decode('"\u26a0\ufe0f"') . "\tFehler\n\n";
        if (!$this->ReadPropertyBoolean('UseScheduleAction')) {
            echo $warning . 'Es wird kein Wochenplan verwendet!';
            return;
        }
        $id = @$this->GetIDForIdent('Weekplan');
        if ($id == 0 || !@IPS_ObjectExists($id)) {
            echo $warning . 'Der zugewiesene Wochenplan ist nicht vorhanden!';
            return;
        }
        if (@IPS_ObjectExists($id)) {
            $event = IPS_GetEvent($id);
            if ($event['EventActive'] != 1) {
                echo $warning . 'Der Wochenplan ist zur Zeit inaktiv!';
            } else {
                $actionID = $this->DetermineActionID();
                $actionName = $warning . 'Es wurde keine Aktion gefunden!';
                foreach ($event['ScheduleActions'] as $action) {
                    if ($action['ID'] === $actionID) {
                        $actionName = json_decode('"\u2705"') . "\tAktuelle Aktion\n\nID " . $actionID . ' = ' . $action['Name'];
                    }
                }
                echo $actionName;
            }
        }
    }

    /**
     * Executes the action of the scheduled action
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteScheduleAction(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseScheduleAction')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Wochenplan verwendet!', 0);
            return;
        }
        $id = @$this->GetIDForIdent('Weekplan');
        if ($id != 0 && @IPS_ObjectExists($id)) {
            //Check schedule action
            $event = IPS_GetEvent($id);
            if ($event['EventActive'] != 1) {
                $text = 'Abbruch, der Wochenplan ist inaktiv!';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $this->LogMessage('ID ' . $this->InstanceID . ', ' . $text, KL_WARNING);
                return;
            }
            $this->SendDebug(__FUNCTION__, 'Der Wochenplan hat ausgelöst.', 0);
            $actionID = $this->DetermineActionID();
            switch ($actionID) {
                case 1:
                    $toggleAction = $this->ReadPropertyInteger('ScheduleActionToggleActionID1');
                    $state = false;
                    if ($toggleAction == 1) {
                        $state = true;
                    }
                    break;

                case 2:
                    $toggleAction = $this->ReadPropertyInteger('ScheduleActionToggleActionID2');
                    $state = false;
                    if ($toggleAction == 1) {
                        $state = true;
                    }
                    break;

            }
            if (isset($state)) {
                $this->ToggleState($state);
            }
        }
    }

    #################### Private

    /**
     * Determines the action id of a scheduled action
     *
     * @return int
     * n =  Action ID
     *
     * @throws Exception
     */
    private function DetermineActionID(): int
    {
        $actionID = 0;
        $timestamp = time();
        $searchTime = date('H', $timestamp) * 3600 + date('i', $timestamp) * 60 + date('s', $timestamp);
        $weekDay = date('N', $timestamp);
        $id = @$this->GetIDForIdent('Weekplan');
        if ($id != 0 && @IPS_ObjectExists($id)) {
            $event = IPS_GetEvent($id);
            foreach ($event['ScheduleGroups'] as $group) {
                if (($group['Days'] & pow(2, $weekDay - 1)) > 0) {
                    $points = $group['Points'];
                    foreach ($points as $point) {
                        $startTime = $point['Start']['Hour'] * 3600 + $point['Start']['Minute'] * 60 + $point['Start']['Second'];
                        if ($startTime <= $searchTime) {
                            $actionID = $point['ActionID'];
                        }
                    }
                }
            }
        }
        return $actionID;
    }
}