<?php

declare(strict_types=1);

trait TSW_Sunset
{
    /**
     * Executes the sunset action.
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteSunsetAction(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseSunset')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Sonnenuntergang verwendet!', 0);
            return;
        }
        $id = $this->ReadPropertyInteger('Sunset');
        if ($id != 0 && @IPS_ObjectExists($id)) {
            $this->SendDebug(__FUNCTION__, 'Der Sonnenuntergang hat ausgelöst.', 0);
            $state = boolval($this->ReadPropertyInteger('SunsetToggleAction'));
            $this->ToggleState($state);
        }
    }

    #################### Private

    /**
     * Checks the sunset.
     *
     * @return void
     * @throws Exception
     */
    private function CheckSunset(): void
    {
        if ($this->CheckMaintenance()) {
            return;
        }
        if (!$this->CheckAutomaticMode()) {
            return;
        }
        if (!$this->ReadPropertyBoolean('UseSunset')) {
            $this->SendDebug(__FUNCTION__, 'Es wird kein Sonnenuntergang verwendet!', 0);
            return;
        }
        $this->SendDebug(__FUNCTION__, 'Es wird geprüft, ob es Sonnenuntergang ist', 0);
        $now = time();
        
        $sunriseOffset = 0;
        $sunsetOffset = 0;
        
        $sunriseOffsetID = @$this->GetIDForIdent('OffsetSunrise');
        if ($sunriseOffsetID)
        {
            $sunriseOffset = GetValueInteger($sunriseOffsetID)  * 60;
        }
        $sunsetOffsetID = @$this->GetIDForIdent('OffsetSunset');
        if ($sunsetOffsetID)
        {
            $sunsetOffset = GetValueInteger($sunsetOffsetID) * 60;
        }
        
        
        $sunsetID = $this->ReadPropertyInteger('Sunset');
        if ($sunsetID != 0 && @IPS_ObjectExists($sunsetID)) {
            if ($this->ReadPropertyBoolean('UseSunrise')) {
                $sunriseID = $this->ReadPropertyInteger('Sunrise');
                if ($sunriseID != 0 && @IPS_ObjectExists($sunriseID)) {
                    $sunriseTime = GetValueInteger($sunriseID) + $sunriseOffset;
                    $sunsetTime = GetValueInteger($sunsetID) + $sunsetOffset;
                    $sunrise = $sunriseTime - $now;
                    $sunset = $sunsetTime - $now;
					
					$this->SendDebug(__FUNCTION__, ' Sunrise:' . $sunrise . ' Sunset:' . $sunset , 0);
                    if ($sunset < 0 && $sunrise > $sunset) {
                        $this->SendDebug(__FUNCTION__, 'Es ist Sonnenuntergang.', 0);
                        $this->ExecuteSunsetAction();
                    }
                }
            }
        }
    }
}