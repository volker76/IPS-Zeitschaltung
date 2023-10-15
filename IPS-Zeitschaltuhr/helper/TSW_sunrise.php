<?php

declare(strict_types=1);

trait TSW_Sunrise
{
    /**
     * Executes the sunrise action.
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteSunriseAction(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseSunrise')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Sonnenaufgang verwendet!', 0);
            return;
        }
        $id = $this->ReadPropertyInteger('Sunrise');
        if ($id != 0 && @IPS_ObjectExists($id)) {
            $this->SendDebug(__FUNCTION__, 'Der Sonnenaufgang hat ausgelöst.', 0);
            $state = boolval($this->ReadPropertyInteger('SunriseToggleAction'));
            $this->ToggleState($state);
        }
    }

    #################### Private

    /**
     * Checks the sunrise
     *
     * @return void
     * @throws Exception
     */
    private function CheckSunrise(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseSunrise')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Sonnenaufgang verwendet!', 0);
            return;
        }
        $this->SendDebug(__FUNCTION__, 'Es wird geprüft, ob es Sonnenaufgang ist', 0);
        $now = time();
        $sunriseOffset = 0;
        $sunsetOffset = 0;
        
        $sunriseOffsetID = @$this->GetIDForIdent('OffsetSunrise');
        if ($sunriseOffsetID)
        {
            $sunriseOffset = GetValueInteger($sunriseOffsetID) * 60;
        }
        $sunsetOffsetID = @$this->GetIDForIdent('OffsetSunset');
        if ($sunsetOffsetID)
        {
            $sunsetOffset = GetValueInteger($sunsetOffsetID) * 60;
        }
        
        $sunriseID = $this->ReadPropertyInteger('Sunrise');
        if ($sunriseID != 0 && @IPS_ObjectExists($sunriseID)) {
            if ($this->ReadPropertyBoolean('UseSunset')) {
                $sunsetID = $this->ReadPropertyInteger('Sunset');
                if ($sunsetID != 0 && @IPS_ObjectExists($sunsetID)) {
                    $sunriseTime = GetValueInteger($sunriseID) + $sunriseOffset;
                    $sunsetTime = GetValueInteger($sunsetID) + $sunsetOffset;
                    $sunrise = $sunriseTime - $now;
                    $sunset = $sunsetTime - $now;
                    if ($sunset < $sunrise) {
                        $this->SendDebug(__FUNCTION__, 'Es ist Sonnenaufgang.', 0);
                        $this->ExecuteSunriseAction();
                    }
                }
            }
        }
    }
}