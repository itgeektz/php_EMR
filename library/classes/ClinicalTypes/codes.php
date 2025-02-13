<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class Codes 
{
    const ICD9 = 'ICD9';
    const CUSTOM = 'CUSTOM';
    const OPTION_ID = 'OPTION_ID'; // This  code maps to an option_id in the list_options table.  The list_id is in the class.
    
    public static function lookup( $clinicalType, $codeType = null )
    {
        if ( $codeType != null ) {
            if ( isset( self::$_codes[$clinicalType][$codeType] ) ) {
              return self::$_codes[$clinicalType][$codeType];
            }
        } else if ( isset( self::$_codes[$clinicalType] ) ) {
            return self::$_codes[$clinicalType];
        }
         
        return array();
    }
    
    protected static $_codes = array(
        Allergy::EGGS => array(
            'ICD9' => array( 'V15.03' )
        ),
        Allergy::DTAP_VAC => array(
            'RXNORM' => array( '204525','205257','205259','260122','359454','562372','795926','795930','795938','795939','795942','798309','798347','802049','805375','805379','806587','806593','807277','807415','824542','829894','829971','829987','830518','830549','830555','830893','835879' )
        ),
        Allergy::HIB => array(
            'RXNORM' => array( '260123','798282','798348','798440','798447','860904' )
        ),
        Allergy::IPV => array(
            'RXNORM' => array( '763103', '866820' )
        ),
        Allergy::NEOMYCIN => array(
            'RXNORM' => array( 'C0988018','C1123055','C1123343','C1123363','C1124224','C1127589','C1134215','C1140030','C1140542','C1186980','C1704126','C272305' )
        ),
        Allergy::POLYMYXIN => array(
            'RxNORM' => array( 'C0988924','C1123393','C1125567','C1133048','C2729553' )
        ),
        Allergy::STREPTOMYCIN => array(
            'RXNORM' => array( 'C1134151' )
        ),
        Communication::COUNS_NUTRITION => array(
            'ICD9' => array( 'V65.3' ) 
        ),
        Communication::COUNS_PHYS_ACTIVITY => array(
            'ICD9' => array( 'V65.41' ) 
        ),
        Diagnosis::ASYMPTOMATIC_HIV => array(
            'ICD9' => array( '042','V08' )
        ),
        Diagnosis::CANCER_LYMPH_HIST => array(
            'ICD9' => array( '201','202','203' )
        ),
        Diagnosis::DIABETES => array(
            'ICD9' => array( '250','250.0','250.00','250.01','250.02','250.03','250.10','250.11','250.12','250.13','250.20','250.21','250.22','250.23','250.30','250.31','250.32','250.33','250.4','250.40','250.41','250.42','250.43','250.50','250.51','250.52','250.53','250.60','250.61','250.62','250.63','250.7','250.70','250.71','250.72','250.73','250.8','250.80','250.81','250.82','250.83','250.9','250.90','250.91','250.92','250.93','357.2','362.0','362.01','362.02','362.03','362.04','362.05','362.06','362.07','366.41','648.0','648.00','648.01','648.02','648.03','648.04')
        ),
        Diagnosis::ENCEPHALOPATHY => array(
            'ICD9' => array( '323.51' )
        ),
        Diagnosis::HEP_A => array(
            'ICD9' => array( '070.0','070.1' )
        ),
        Diagnosis::HEP_B => array(
            'ICD9' => array( '070.2','070.3','V02.61' )
        ),
        Diagnosis::PREGNANCY => array(
    		'ICD9' => array( '630','631','632','633','633.0','633.00','633.01','633.1','633.10','633.11','633.2','633.20','633.21','633.8','633.80','633.81','633.9','633.90','633.91','634','634.0','634.00','634.01','634.02','634.1','634.10','634.11','634.12','634.2','634.20','634.21','634.22','634.3','634.30','634.31','634.32','634.4','634.40','634.41','634.42','634.5','634.50','634.51','634.52','634.6','634.60','634.61','634.62','634.7','634.70','634.71','634.72','634.8','634.80','634.81','634.82','634.9','634.90','634.91','634.92','635','635.0','635.00','635.01','635.02','635.1','635.10','635.11','635.12','635.2','635.20','635.21','635.22','635.3','635.30','635.31','635.32','635.4','635.40','635.41','635.42','635.5','635.50','635.51','635.52','635.6','635.60','635.61','635.62','635.7','635.70','635.71','635.72','635.8','635.80','635.81','635.82','635.9','635.90','635.91','635.92','636','636.0','636.00','636.01','636.02','636.1','636.10','636.11','636.12','636.2','636.20','636.21','636.22','636.3','636.30','636.31','636.32','636.4','636.40','636.41','636.42','636.5','636.50','636.51','636.52','636.6','636.60','636.61','636.62','636.7','636.70','636.71','636.72','636.8','636.80','636.81','636.82','636.9','636.90','636.91','636.92','637','637.0','637.00','637.01','637.02','637.1','637.10','637.11','637.12','637.2','637.20','637.21','637.22','637.3','637.30','637.31','637.32','637.4','637.40','637.41','637.42','637.5','637.50','637.51','637.52','637.6','637.60','637.61','637.62','637.7','637.70','637.71','637.72','637.8','637.80','637.81','637.82','637.9','637.90','637.91','637.92','638','638.0','638.1','638.2','638.3','638.4','638.5','638.6','638.7','638.8','638.9','639','639.0','639.1','639.2','639.3','639.4','639.5','639.6','639.8','639.9','640','640.0','640.00','640.01','640.03','640.8','640.80','640.81','640.83','640.9','640.90','640.91','640.93','641','641.0','641.00','641.01','641.03','641.1','641.10','641.11','641.13','641.2','641.20','641.21','641.23','641.3','641.30','641.31','641.33','641.8','641.80','641.81','641.83','641.9','641.90','641.91','641.93','642','642.0','642.00','642.01','642.02','642.03','642.04','642.1','642.10','642.11','642.12','642.13','642.14','642.2','642.20','642.21','642.22','642.23','642.24','642.3','642.30','642.31','642.32','642.33','642.34','642.4','642.40','642.41','642.42','642.43','642.44','642.5','642.50','642.51','642.52','642.53','642.54','642.6','642.60','642.61','642.62','642.63','642.64','642.7','642.70','642.71','642.72','642.73','642.74','642.9','642.90','642.91','642.92','642.93','642.94','643','643.0','643.00','643.01','643.03','643.1','643.10','643.11','643.13','643.2','643.20','643.21','643.23','643.8','643.80','643.81','643.83','643.9','643.90','643.91','643.93','644','644.0','644.00','644.03','644.1','644.10','644.13','644.2','644.20','644.21','645','645.1','645.10','645.11','645.13','645.2','645.20','645.21','645.23','646','646.0','646.00','646.01','646.03','646.1','646.10','646.11','646.12','646.13','646.14','646.2','646.20','646.21','646.22','646.23','646.24','646.3','646.30','646.31','646.33','646.4','646.40','646.41','646.42','646.43','646.44','646.5','646.50','646.51','646.52','646.53','646.54','646.6','646.60','646.61','646.62','646.63','646.64','646.7','646.70','646.71','646.73','646.8','646.80','646.81','646.82','646.83','646.84','646.9','646.90','646.91','646.93','647','647.0','647.00','647.01','647.02','647.03','647.04','647.1','647.10','647.11','647.12','647.13','647.14','647.2','647.20','647.21','647.22','647.23','647.24','647.3','647.30','647.31','647.32','647.33','647.34','647.4','647.40','647.41','647.42','647.43','647.44','647.5','647.50','647.51','647.52','647.53','647.54','647.6','647.60','647.61','647.62','647.63','647.64','647.8','647.80','647.81','647.82','647.83','647.84','647.9','647.90','647.91','647.92','647.93','647.94','648','648.0','648.00','648.01','648.02','648.03','648.04','648.1','648.10','648.11','648.12','648.13','648.14','648.2','648.20','648.21','648.22','648.23','648.24','648.3','648.30','648.31','648.32','648.33','648.34','648.4','648.40','648.41','648.42','648.43','648.44','648.5','648.50','648.51','648.52','648.53','648.54','648.6','648.60','648.61','648.62','648.63','648.64','648.7','648.70','648.71','648.72','648.73','648.74','648.8','648.80','648.81','648.82','648.83','648.84','648.9','648.90','648.91','648.92','648.93','648.94','649','649.0','649.00','649.01','649.02','649.03','649.04','649.1','649.10','649.11','649.12','649.13','649.14','649.2','649.20','649.21','649.22','649.23','649.24','649.3','649.30','649.31','649.32','649.33','649.34','649.4','649.40','649.41','649.42','649.43','649.44','649.5','649.50','649.51','649.53','649.6','649.60','649.61','649.62','649.63','649.64','649.7','649.70','649.71','649.73','650','651','651.0','651.00','651.01','651.03','651.1','651.10','651.11','651.13','651.2','651.20','651.21','651.23','651.3','651.30','651.31','651.33','651.4','651.40','651.41','651.43','651.5','651.50','651.51','651.53','651.6','651.60','651.61','651.63','651.7','651.70','651.71','651.73','651.8','651.80','651.81','651.83','651.9','651.90','651.91','651.93','652','652.0','652.00','652.01','652.03','652.1','652.10','652.11','652.13','652.2','652.20','652.21','652.23','652.3','652.30','652.31','652.33','652.4','652.40','652.41','652.43','652.5','652.50','652.51','652.53','652.6','652.60','652.61','652.63','652.7','652.70','652.71','652.73','652.8','652.80','652.81','652.83','652.9','652.90','652.91','652.93','653','653.0','653.00','653.01','653.03','653.1','653.10','653.11','653.13','653.2','653.20','653.21','653.23','653.3','653.30','653.31','653.33','653.4','653.40','653.41','653.43','653.5','653.50','653.51','653.53','653.6','653.60','653.61','653.63','653.7','653.70','653.71','653.73','653.8','653.80','653.81','653.83','653.9','653.90','653.91','653.93','654','654.0','654.00','654.01','654.02','654.03','654.04','654.1','654.10','654.11','654.12','654.13','654.14','654.2','654.20','654.21','654.23','654.3','654.30','654.31','654.32','654.33','654.34','654.4','654.40','654.41','654.42','654.43','654.44','654.5','654.50','654.51','654.52','654.53','654.54','654.6','654.60','654.61','654.62','654.63','654.64','654.7','654.70','654.71','654.72','654.73','654.74','654.8','654.80','654.81','654.82','654.83','654.84','654.9','654.90','654.91','654.92','654.93','654.94','655','655.0','655.00','655.01','655.03','655.1','655.10','655.11','655.13','655.2','655.20','655.21','655.23','655.3','655.30','655.31','655.33','655.4','655.40','655.41','655.43','655.5','655.50','655.51','655.53','655.6','655.60','655.61','655.63','655.7','655.70','655.71','655.73','655.8','655.80','655.81','655.83','655.9','655.90','655.91','655.93','656','656.0','656.00','656.01','656.03','656.1','656.10','656.11','656.13','656.2','656.20','656.21','656.23','656.3','656.30','656.31','656.33','656.4','656.40','656.41','656.43','656.5','656.50','656.51','656.53','656.6','656.60','656.61','656.63','656.7','656.70','656.71','656.73','656.8','656.80','656.81','656.83','656.9','656.90','656.91','656.93','657','657.0','657.00','657.01','657.03','658','658.0','658.00','658.01','658.03','658.1','658.10','658.11','658.13','658.2','658.20','658.21','658.23','658.3','658.30','658.31','658.33','658.4','658.40','658.41','658.43','658.8','658.80','658.81','658.83','658.9','658.90','658.91','658.93','659','659.0','659.00','659.01','659.03','659.1','659.10','659.11','659.13','659.2','659.20','659.21','659.23','659.3','659.30','659.31','659.33','659.4','659.40','659.41','659.43','659.5','659.50','659.51','659.53','659.6','659.60','659.61','659.63','659.7','659.70','659.71','659.73','659.8','659.80','659.81','659.83','659.9','659.90','659.91','659.93','660','660.0','660.00','660.01','660.03','660.1','660.10','660.11','660.13','660.2','660.20','660.21','660.23','660.3','660.30','660.31','660.33','660.4','660.40','660.41','660.43','660.5','660.50','660.51','660.53','660.6','660.60','660.61','660.63','660.7','660.70','660.71','660.73','660.8','660.80','660.81','660.83','660.9','660.90','660.91','660.93','661','661.0','661.00','661.01','661.03','661.1','661.10','661.11','661.13','661.2','661.20','661.21','661.23','661.3','661.30','661.31','661.33','661.4','661.40','661.41','661.43','661.9','661.90','661.91','661.93','662','662.0','662.00','662.01','662.03','662.1','662.10','662.11','662.13','662.2','662.20','662.21','662.23','662.3','662.30','662.31','662.33','663','663.0','663.00','663.01','663.03','663.1','663.10','663.11','663.13','663.2','663.20','663.21','663.23','663.3','663.30','663.31','663.33','663.4','663.40','663.41','663.43','663.5','663.50','663.51','663.53','663.6','663.60','663.61','663.63','663.8','663.80','663.81','663.83','663.9','663.90','663.91','663.93','664','664.0','664.00','664.01','664.04','664.1','664.10','664.11','664.14','664.2','664.20','664.21','664.24','664.3','664.30','664.31','664.34','664.4','664.40','664.41','664.44','664.5','664.50','664.51','664.54','664.6','664.60','664.61','664.64','664.8','664.80','664.81','664.84','664.9','664.90','664.91','664.94','665','665.0','665.00','665.01','665.03','665.1','665.10','665.11','665.2','665.20','665.22','665.24','665.3','665.30','665.31','665.34','665.4','665.40','665.41','665.44','665.5','665.50','665.51','665.54','665.6','665.60','665.61','665.64','665.7','665.70','665.71','665.72','665.74','665.8','665.80','665.81','665.82','665.83','665.84','665.9','665.90','665.91','665.92','665.93','665.94','666','666.0','666.00','666.02','666.04','666.1','666.10','666.12','666.14','666.2','666.20','666.22','666.24','666.3','666.30','666.32','666.34','667','667.0','667.00','667.02','667.04','667.1','667.10','667.12','667.14','668','668.0','668.00','668.01','668.02','668.03','668.04','668.1','668.10','668.11','668.12','668.13','668.14','668.2','668.20','668.21','668.22','668.23','668.24','668.8','668.80','668.81','668.82','668.83','668.84','668.9','668.90','668.91','668.92','668.93','668.94','669','669.0','669.00','669.01','669.02','669.03','669.04','669.1','669.10','669.11','669.12','669.13','669.14','669.2','669.20','669.21','669.22','669.23','669.24','669.3','669.30','669.32','669.34','669.4','669.40','669.41','669.42','669.43','669.44','669.5','669.50','669.51','669.6','669.60','669.61','669.7','669.70','669.71','669.8','669.80','669.81','669.82','669.83','669.84','669.9','669.90','669.91','669.92','669.93','669.94','670','670.0','670.00','670.02','670.04','671','671.0','671.00','671.01','671.02','671.03','671.04','671.1','671.10','671.11','671.12','671.13','671.14','671.2','671.20','671.21','671.22','671.23','671.24','671.3','671.30','671.31','671.33','671.4','671.40','671.42','671.44','671.5','671.50','671.51','671.52','671.53','671.54','671.8','671.80','671.81','671.82','671.83','671.84','671.9','671.90','671.91','671.92','671.93','671.94','672','672.0','672.00','672.02','672.04','673','673.0','673.00','673.01','673.02','673.03','673.04','673.1','673.10','673.11','673.12','673.13','673.14','673.2','673.20','673.21','673.22','673.23','673.24','673.3','673.30','673.31','673.32','673.33','673.34','673.8','673.80','673.81','673.82','673.83','673.84','674','674.0','674.00','674.01','674.02','674.03','674.04','674.1','674.10','674.12','674.14','674.2','674.20','674.22','674.24','674.3','674.30','674.32','674.34','674.4','674.40','674.42','674.44','674.5','674.50','674.51','674.52','674.53','674.54','674.8','674.80','674.82','674.84','674.9','674.90','674.92','674.94','675','675.0','675.00','675.01','675.02','675.03','675.04','675.1','675.10','675.11','675.12','675.13','675.14','675.2','675.20','675.21','675.22','675.23','675.24','675.8','675.80','675.81','675.82','675.83','675.84','675.9','675.90','675.91','675.92','675.93','675.94','676','676.0','676.00','676.01','676.02','676.03','676.04','676.1','676.10','676.11','676.12','676.13','676.14','676.2','676.20','676.21','676.22','676.23','676.24','676.3','676.30','676.31','676.32','676.33','676.34','676.4','676.40','676.41','676.42','676.43','676.44','676.5','676.50','676.51','676.52','676.53','676.54','676.6','676.60','676.61','676.62','676.63','676.64','676.8','676.80','676.81','676.82','676.83','676.84','676.9','676.90','676.91','676.92','676.93','676.94','677','678','678.0','678.00','678.01','678.03','678.1','678.10','678.11','678.13','679','679.0','679.00','679.01','679.02','679.03','679.04','679.1','679.10','679.11','679.12','679.13','679.14','V22','V22.0','V22.1','V22.2','V23','V23.0','V23.1','V23.2','V23.3','V23.4','V23.41','V23.49','V23.5','V23.7','V23.8','V23.81','V23.82','V23.83','V23.84','V23.85','V23.86','V23.89','V23.9','V28','V28.0','V28.1','V28.2','V28.3','V28.4','V28.5','V28.6','V28.8','V28.81','V28.82','V28.89','V28.9' ) 
        ),
        Diagnosis::HYPERTENSION => array(
            'CUSTOM' => array( 'HTN' ),
    		'ICD9' => array( '401.0','401.1','401.9','402.00','402.01','402.10','402.11','402.90','402.91','403.00','403.01','403.10','403.11','403.90','403.91','404.00','404.01','404.02','404.03','404.10','404.11','404.12','404.13','404.90','404.91','404.92','404.93' ) 
        ),
        Diagnosis::ENCEPHALOPATHY => array(
            'ICD9' => array( '323.51' ) 
        ),
        Diagnosis::GESTATIONAL_DIABETES => array(
            'ICD9' => array( '648.8','648.80','648.81','648.82','648.83','648.84' )
        ),
        Diagnosis::IMMUNODEF => array(
            'ICD9' => array( '279' )
        ),
        Diagnosis::LUKEMIA => array(
            'ICD9' => array( '200','202','204','205','206','207','208' )
        ),
        Diagnosis::MEASLES => array(
            'ICD9' => array( '055' )
        ),
        Diagnosis::MULT_MYELOMA => array(
            'ICD9' => array( '203' )
        ),
        Diagnosis::MUMPS => array(
            'ICD9' => array() // TODO
        ),
        Diagnosis::POLYCYSTIC_OVARIES => array(
            'ICD9' => array( '256.4' )
        ),
        Diagnosis::PROG_NEURO_DISORDER => array(
            'ICD9' => array() ,
            'SNOMED' => array( '230363006', '292925004', '292927007', '292992006' )
        ),
        Diagnosis::RUBELLA => array(
            'ICD9' => array() // TODO
        ),
        Diagnosis::STEROID_INDUCED_DIABETES => array(
            'ICD9' => array( '249','249.0','249.00','249.01','249.1','249.10','249.11','249.2','249.20','249.21','249.3','249.30','249.31','249.4','249.40','249.41','249.5','249.50','249.51','249.6','249.60','249.61','249.7','249.70','249.71','249.8','249.80','249.81','249.9','249.90','249.91','251.8','962.0')
        ),
        Diagnosis::VZV => array(
            'ICD9' => array( '052','053' )
        ),
        Encounter::ENC_OUT_PCP_OBGYN => array(
            'ICD9' => array( 'V24','V25','V26','V27','V28','V45.5','V61.5','V61.6','V61.7','V69.2','V72.3','V72.4' ) 
        ),
        Encounter::ENC_OUTPATIENT => array(
            'ICD9' => array( 'V70.0','V70.3','V70.5','V70.6','V70.8','V70.9' ) 
        ),
        Encounter::ENC_PREGNANCY => array(
            'ICD9' => array( 'V24','V24.0','V24.2','V25','V25.01','V25.02','V25.03','V25.09','V26.81','V28','V28.3','V28.81','V28.82','V72.4','V72.40','V72.41','V72.42' ) 
        ),
        LabResult::HB1AC_TEST => array(
            'CPT4' => array( '83036','83037' )
        ),
        LabResult::LDL_TEST => array(
            'CPT4' => array( '80061','83700','83701','83704','83721')
        ),
        Medication::DTAP_VAC => array(
            'CVX' => array( 20, 50, 106, 107, 110, 120, 130, 132 )
        ),
        Medication::HEP_A_VAC => array(
            'CVX' => array( 31, 52, 83, 84, 85, 104)
        ),
        Medication::HEP_B_VAC => array(
            'CVX' => array( 8, 42, 43, 44, 45, 51, 102, 104, 110, 132 )
        ),
        Medication::HIB => array(
            'CVX' => array( 17, 22, 46, 47, 48, 49, 50, 51, 102, 120, 132 )
        ),
        Medication::IPV => array(
            'CVX' => array( 10, 110, 120, 130, 132 )
        ),
        Medication::MEASLES_VAC => array(
            'CVX' => array( 3, 4, 5, 94 )
        ),
        Medication::MMR => array(
            'CVX' => array( 3, 94 )
        ),
        Medication::MUMPS_VAC => array(
            'CVX' => array( 3, 7, 38, 94 )
        ),
        Medication::PNEUMOCOCCAL_VAC => array(
            'CVX' => array ( 33, 100, 109, 133 )
        ),
        Medication::RUBELLA_VAC => array(
            'CVX' => array( 3, 4, 6, 38, 94 )
        ),
        Medication::ROTAVIRUS_VAC => array(
            'CVX' => array( 74, 116, 119, 122 )
        ),
        Medication::VZV => array(
            'CVX' => array( 21, 21 )
        ),
        Medication::INFLUENZA_VAC => array(
            'CVX' => array( 15, 16, 88, 111, 125, 126, 127, 128, 135, 140, 141, 144 )
        ),
        PhysicalExam::FINDING_BMI_PERC => array(
            'ICD9' => array( 'V85.5','V85.51','V85.52','V85.53','V85.54' ) 
        ), 
    );
}
