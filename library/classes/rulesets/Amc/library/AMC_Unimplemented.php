<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'AbstractAmcReport.php' );

class AMC_Unimplemented extends AbstractAmcReport implements RsUnimplementedIF
{   
    public function __construct()
    {
        parent::__construct( array(), array(), null );
    }

    public function getObjectToCount()
    {
        return null;
    }
    
    public function createDenominator() 
    {
        return null;
    }
    
    public function createNumerator()
    {
        return null;
    }
}
