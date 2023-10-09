<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/SymconModulHelper/DebugHelper.php';

class TimerSwitch extends IPSModule
{
    public static $Variables = [
        ['TSW_Active', 'Active', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['TSW_Start1', 'StartWindow 1', VARIABLETYPE_INTEGER, '~UnixTimestampTime', [], '', true, true]
    ];

    public function Create()
    {
        parent::Create();
        

        $this->RegisterPropertyInteger('SwitchOutput', '');
        
        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Devices'      => $Variable[4],
                'DeviceType'   => $Variable[5],
                'Action'       => $Variable[6],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[7]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        
        $SwitchOutput = $this->ReadPropertyInteger('SwitchOutput');
        

        $NewRows = static::$Variables;
        $NewPos = 0;
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            $Devices = $Variable['Devices'];
            if ($Variable['Devices'] == null) {
                $Devices = [];
            }

            $Device = '';
            $Device = @$this->ReadPropertyString('Device');

            $DeviceType = '';
            $DeviceType = @$this->ReadPropertyString('DeviceType');

            $VariableActive = $Variable['Keep'] && (in_array($Device, $Devices) || empty($Devices)) && (($DeviceType == $Variable['DeviceType']) || $Variable['DeviceType'] == '');

            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $VariableActive);
            if ($Variable['Action'] && $VariableActive) {
                $this->EnableAction($Variable['Ident']);
            }
            foreach ($NewRows as $Index => $Row) {
                if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                    unset($NewRows[$Index]);
                }
            }
            if ($NewPos < $Variable['Pos']) {
                $NewPos = $Variable['Pos'];
            }
        }

        if (count($NewRows) != 0) {
            foreach ($NewRows as $NewVariable) {
                $Variables[] = [
                    'Ident'        => str_replace(' ', '', $NewVariable[0]),
                    'Name'         => $this->Translate($NewVariable[1]),
                    'VarType'      => $NewVariable[2],
                    'Profile'      => $NewVariable[3],
                    'Devices'      => $NewVariable[4],
                    'DeviceType'   => $NewVariable[5],
                    'Action'       => $NewVariable[6],
                    'Pos'          => ++$NewPos,
                    'Keep'         => $NewVariable[7]
                ];
            }
            IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
            IPS_ApplyChanges($this->InstanceID);
            return;
        }
    }

    protected function SetValue($Ident, $Value)
    {
        if (@$this->GetIDForIdent($Ident)) {
            $this->SendDebug('SetValue :: ' . $Ident, $Value, 0);
            parent::SetValue($Ident, $Value);
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Tuya_State':
                
                break;
            }
    }


}