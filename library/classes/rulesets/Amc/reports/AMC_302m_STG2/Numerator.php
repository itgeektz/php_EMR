<?php
/**
 *
 * AMC 302m STAGE2 Numerator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */
class AMC_302m_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_302m_STG2 Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
        // Is patient provided patient specific education during the report period.
        // Check for any patient specific education instances.
        $item = sqlQuery("SELECT * FROM `amc_misc_data` as amc, `form_encounter` as enc " .
                         "WHERE enc.pid = amc.pid AND enc.pid = ? " .
                         "AND amc.map_category = 'form_encounter' " .
                         "AND enc.encounter = amc.map_id " .
                         "AND `amc_id` = 'patient_edu_amc' " .
                         "AND enc.date >= ? " .
                         "AND enc.date <= ?", array($patient->id,$beginDate,$endDate) );

        if ( !(empty($item)) ) {
          return true;
        }
        else {
          return false;
        }
    }
}
