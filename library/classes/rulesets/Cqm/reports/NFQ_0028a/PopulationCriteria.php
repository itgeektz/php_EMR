<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0028a_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{    
    public function getTitle()
    {
        return "Population Criteria";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0028a_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        return new NFQ_0028a_Numerator();
    }
    
    public function createDenominator()
    {
        return new DenominatorAllPatients();
    }
    
    public function createExclusion()
    {
        return new ExclusionsNone();
    }
}
