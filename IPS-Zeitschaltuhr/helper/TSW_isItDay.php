<?php

declare(strict_types=1);

trait TSW_IsItDay
{
    /**
     * Executes the is it day action.
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteIsItDayAction(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseIsItDay')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Ist es Tag verwendet!', 0);
            return;
        }
        $id = $this->ReadPropertyInteger('IsItDay');
        if ($id != 0 && @IPS_ObjectExists($id)) {
            $this->SendDebug(__FUNCTION__, 'Ist es Tag hat ausgelöst.', 0);
            $toggleAction = !$this->ReadPropertyInteger('IsItDayToggleAction');
            if (GetValueBoolean($id)) {
                $toggleAction = $this->ReadPropertyInteger('IsItDayToggleAction');
            }
            $state = false;
            if ($toggleAction == 1) {
                $state = true;
            }
            $this->ToggleState($state);
        }
    }
}