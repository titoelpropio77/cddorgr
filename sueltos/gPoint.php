<?php
 
/**
* PHP class to convert Latitude & Longitude coordinates into UTM & Lambert Conic Conformal Northing/Easting coordinates.
*
* This class encapsulates the methods for representing a geographic point on the earth in three different coordinate systema. Lat/Long, UTM and Lambert Conic Conformal.
*
* Code for datum and UTM conversion was converted from C++ code written by Chuck Gantz (chuck.gantz@globalstar.com) from http://www.gpsy.com/gpsinfo/geotoutm/
* This code was converted into PHP by Brenor Brophy (brenor@sbcglobal.net) and later refactored for PHP 5.3 by Hans Duedal (hd@onlinecity.dk).
*
* @author chuck.gantz@globalstar.com, brenor@sbcglobal.net, hd@onlinecity.dk
* @version 1.0
*
* COPYRIGHT (c) 2005, 2006, 2007, 2008 BRENOR BROPHY
* The source code included in this package is free software; you can
* redistribute it and/or modify it under the terms of the GNU General Public
* License as published by the Free Software Foundation. This license can be read at:
*
* http://www.opensource.org/licenses/gpl-license.php
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*
* @link http://www.gpsy.com/gpsinfo/geotoutm/
* @link http://www.phpclasses.org/browse/file/10671.html
* @link https://gist.github.com/840476
*/
class gPoint
{
/**
* Reference ellipsoids derived from Peter H. Dana's website:
* http://www.utexas.edu/depts/grg/gcraft/notes/datum/elist.html
* Department of Geography, University of Texas at Austin
* Internet: pdana@mail.utexas.edu 3/22/95
* Source:
* Defense Mapping Agency. 1987b. DMA Technical Report: Supplement to Department of Defense World Geodetic System 1984 Technical Report. Part I and II.
* Washington, DC: Defense Mapping Agency
* Alternative names added in for easy compatibility by hd@onlinecity.dk
*
* @var array - format ("Ellipsoid name" => array(Equatorial Radius, square of eccentricity))
*/
public static $ellipsoid = array(	
"Airy"	=>array (6377563, 0.00667054),
"Australian National"	=>array	(6378160, 0.006694542),
"Bessel 1841"	=>array	(6377397, 0.006674372),
"Bessel 1841 Nambia"	=>array	(6377484, 0.006674372),
"Clarke 1866"	=>array	(6378206, 0.006768658),
"Clarke 1880"	=>array	(6378249, 0.006803511),
"Everest"	=>array	(6377276, 0.006637847),
"Fischer 1960 Mercury"	=>array (6378166, 0.006693422),
"Fischer 1968"	=>array (6378150, 0.006693422),
"GRS 1967"	=>array	(6378160, 0.006694605),
"GRS 1980"	=>array	(6378137, 0.00669438),
"Helmert 1906"	=>array	(6378200, 0.006693422),
"Hough"	=>array	(6378270, 0.00672267),
"International"	=>array	(6378388, 0.00672267),
"Krassovsky"	=>array	(6378245, 0.006693422),
"Modified Airy"	=>array	(6377340, 0.00667054),
"Modified Everest"	=>array	(6377304, 0.006637847),
"Modified Fischer 1960"	=>array	(6378155, 0.006693422),
"South American 1969"	=>array	(6378160, 0.006694542),
"WGS 60"	=>array (6378165, 0.006693422),
"WGS 66"	=>array (6378145, 0.006694542),
"WGS 72"	=>array (6378135, 0.006694318),
"WGS 84"	=>array (6378137, 0.00669438),
// Alternative names, added in for easy compatibility by hd@onlinecity.dk
"ED50"	=>array	(6378388, 0.00672267), // International Ellipsoid
"EUREF89"	=>array	(6378137, 0.00669438), // Max deviation from WGS 84 is 40 cm/km see (in danish) http://www2.kms.dk/C1256AED004E87BA/(AllDocsByDocId)/3382517647F695C9C1256BC700265CE7
"ETRS89"	=>array	(6378137, 0.00669438) // Same as EUREF89
);
 
// Properties
protected $a;	// Equatorial Radius
protected $e2;	// Square of eccentricity
protected $datum;	// Selected datum
protected $Xp, $Yp;	// X,Y pixel location
protected $lat, $long;	// Latitude & Longitude of the point
protected $utmNorthing, $utmEasting, $utmZone;	// UTM Coordinates of the point
protected $lccNorthing, $lccEasting;	// Lambert coordinates of the point
protected $falseNorthing, $falseEasting;	// Origin coordinates for Lambert Projection
protected $latOfOrigin;	// For Lambert Projection
protected $longOfOrigin;	// For Lambert Projection
protected $firstStdParallel;	// For lambert Projection
protected $secondStdParallel;	// For lambert Projection
 
/**
* Constructs the object and sets the datum
*
* @param string $datum
*/
public function __construct($datum='WGS 84')	// Default datum is WGS 84
{
$this->a = self::$ellipsoid[$datum][0];	// Set datum Equatorial Radius
$this->e2 = self::$ellipsoid[$datum][1];	// Set datum Square of eccentricity
$this->datum = $datum;	// Save the datum
}
/**
* Set the datum
*
* @param string $datum
*/
public function setDatum($datum='WGS 84')
{
$this->a = self::$ellipsoid[$datum][0];	// Set datum Equatorial Radius
$this->e2 = self::$ellipsoid[$datum][1];	// Set datum Square of eccentricity
$this->datum = $datum;	// Save the datum
}
/**
* Set X & Y pixel of the point (used if it is being drawn on an image)
*
* @param integer $x
* @param integer $y
*/
public function setXY($x, $y)
{
$this->Xp = $x; $this->Yp = $y;
}
/**
* Get X pixel location
*/
public function Xp() {
return $this->Xp;
}
/**
* Get Y pixel location
*/
public function Yp() {
return $this->Yp;
}
/**
* Set/Get/Output Longitude & Latitude of the point
* @param float $long
* @param float $lat
*/
public function setLongLat($long, $lat)
{
$this->long = $long;
$this->lat = $lat;
}
/**
* Get latitude
*
*/
public function Lat() {
return $this->lat;
}
/**
* Get longitude
*
*/
public function Long() {
return $this->long;
}
/**
* Print latitude/longitude
*
*/
public function printLatLong() {
printf("Latitude: %1.5f Longitude: %1.5f",$this->lat, $this->long);
}
/**
* Set Universal Transverse Mercator Coordinates
*
* @param integer $easting
* @param integer $northing
* @param string $zone
*/
public function setUTM($easting, $northing, $zone='')	// Zone is optional
{
	$this->utmNorthing = $northing;
	$this->utmEasting = $easting;
	$this->utmZone = $zone;
}
/**
* Get utm northing
*
*/
public function N() {
return $this->utmNorthing;
}
/**
* Get utm easting
*
*/
public function E() {
return $this->utmEasting;
}
/**
* Get utm zone
*/
public function Z() {
return $this->utmZone;
}
/**
* Print UTM coordinates
*
*/
public function printUTM() {
print( "Northing: ".(int)$this->utmNorthing.", Easting: ".(int)$this->utmEasting.", Zone: ".$this->utmZone);
}
/**
* Set the lambert coordinates
*
* @param integer $northing
* @param integer $easting
*/
public function setLambert($northing, $easting)
{
$this->lccNorthing = $northing;
$this->lccEasting = $easting;
}
/**
* Get lccNorthing
*/
public function lccN() {
return $this->lccNorthing;
}
/**
* Get lccEasting
*/
public function lccE() {
return $this->lccEasting;
}
/**
* Print lambert coordinates
*
*/
public function printLambert() {
print( "Northing: ".(int)$this->lccNorthing.", Easting: ".(int)$this->lccEasting);
}
/**
* Convert Longitude/Latitude to UTM
*
* Equations from USGS Bulletin 1532
* East Longitudes are positive, West longitudes are negative.
* North latitudes are positive, South latitudes are negative
* Lat and Long are in decimal degrees
* Written by Chuck Gantz- chuck.gantz@globalstar.com, converted to PHP by
* Brenor Brophy, brenor@sbcglobal.net
*
* UTM coordinates are useful when dealing with paper maps. Basically the
* map will can cover a single UTM zone which is 6 degrees on longitude.
* So you really don't care about an object crossing two zones. You just get a
* second map of the other zone. However, if you happen to live in a place that
* straddles two zones (For example the Santa Babara area in CA straddles zone 10
* and zone 11) Then it can become a real pain having to have two maps all the time.
* So relatively small parts of the world (like say California) creat their own
* version of UTM coordinates that are adjusted to conver the whole area of interest
* on a single map. These are called state grids. The projection system is the
* usually same as UTM (i.e. Transverse Mercator), but the central meridian
* aka Longitude of Origin is selected to suit the logitude of the area being
* mapped (like being moved to the central meridian of the area) and the grid
* may cover more than the 6 degrees of longitude found on a UTM map. Areas
* that are wide rather than long - think Montana as an example. May still
* have to have a couple of maps to cover the whole state because TM projection
* looses accuracy as you move further away from the Longitude of Origin, 15 degrees
* is usually the limit.
*
* Now, in the case where we want to generate electronic maps that may be
* placed pretty much anywhere on the globe we really don't to deal with the
* issue of UTM zones in our coordinate system. We would really just like a
* grid that is fully contigious over the area of the map we are drawing. Similiar
* to the state grid, but local to the area we are interested in. I call this
* Local Transverse Mercator and I have modified the function below to also
* make this conversion. If you pass a Longitude value to the function as $LongOrigin
* then that is the Longitude of Origin that will be used for the projection.
* Easting coordinates will be returned (in meters) relative to that line of
* longitude - So an Easting coordinate for a point located East of the longitude
* of origin will be a positive value in meters, an Easting coordinate for a point
* West of the longitude of Origin will have a negative value in meters. Northings
* will always be returned in meters from the equator same as the UTM system. The
* UTMZone value will be valid for Long/Lat given - thought it is not meaningful
* in the context of Local TM. If a NULL value is passed for $LongOrigin
* then the standard UTM coordinates are calculated.
*
* @param float $LongOrigin
*/
public function convertLLtoTM($LongOrigin)
{
$k0 = 0.9996;
$falseEasting = 0.0;
 
//Make sure the longitude is between -180.00 .. 179.9
$LongTemp = ($this->long+180)-(integer)(($this->long+180)/360)*360-180; // -180.00 .. 179.9;
$LatRad = deg2rad($this->lat);
$LongRad = deg2rad($LongTemp);
 
if (!$LongOrigin) { // Do a standard UTM conversion - so findout what zone the point is in
$ZoneNumber = (integer)(($LongTemp + 180)/6) + 1;
if( $this->lat >= 56.0 && $this->lat < 64.0 && $LongTemp >= 3.0 && $LongTemp < 12.0 ) $ZoneNumber = 32;
// Special zones for Svalbard
if( $this->lat >= 72.0 && $this->lat < 84.0 ) {
if($LongTemp >= 0.0 && $LongTemp < 9.0) {
$ZoneNumber = 31;	
} else if($LongTemp >= 9.0 && $LongTemp < 21.0) {
$ZoneNumber = 33;	
} else if($LongTemp >= 21.0 && $LongTemp < 33.0) {
$ZoneNumber = 35;	
} else if($LongTemp >= 33.0 && $LongTemp < 42.0) {
$ZoneNumber = 37;	
}
}
$LongOrigin = ($ZoneNumber - 1)*6 - 180 + 3; //+3 puts origin in middle of zone
//compute the UTM Zone from the latitude and longitude
$this->utmZone = sprintf("%d%s", $ZoneNumber, $this->UTMLetterDesignator());
// We also need to set the false Easting value adjust the UTM easting coordinate
$falseEasting = 500000.0;
}
$LongOriginRad = deg2rad($LongOrigin);
 
$eccPrimeSquared = ($this->e2)/(1-$this->e2);
 
$N = $this->a/sqrt(1-$this->e2*sin($LatRad)*sin($LatRad));
$T = tan($LatRad)*tan($LatRad);
$C = $eccPrimeSquared*cos($LatRad)*cos($LatRad);
$A = cos($LatRad)*($LongRad-$LongOriginRad);
 
$M = $this->a*((1	- $this->e2/4	- 3*$this->e2*$this->e2/64	- 5*$this->e2*$this->e2*$this->e2/256)*$LatRad
- (3*$this->e2/8	+ 3*$this->e2*$this->e2/32	+ 45*$this->e2*$this->e2*$this->e2/1024)*sin(2*$LatRad)
+ (15*$this->e2*$this->e2/256 + 45*$this->e2*$this->e2*$this->e2/1024)*sin(4*$LatRad)
- (35*$this->e2*$this->e2*$this->e2/3072)*sin(6*$LatRad));
$this->utmEasting = ($k0*$N*($A+(1-$T+$C)*$A*$A*$A/6
+ (5-18*$T+$T*$T+72*$C-58*$eccPrimeSquared)*$A*$A*$A*$A*$A/120)
+ $falseEasting);
 
$this->utmNorthing = ($k0*($M+$N*tan($LatRad)*($A*$A/2+(5-$T+9*$C+4*$C*$C)*$A*$A*$A*$A/24
+ (61-58*$T+$T*$T+600*$C-330*$eccPrimeSquared)*$A*$A*$A*$A*$A*$A/720)));
if($this->lat < 0) $this->utmNorthing += 10000000.0; //10000000 meter offset for southern hemisphere
}
 
/**
* This routine determines the correct UTM letter designator for the given latitude
* returns 'Z' if latitude is outside the UTM limits of 84N to 80S
* Written by Chuck Gantz- chuck.gantz@globalstar.com, converted to PHP by Brenor Brophy, brenor@sbcglobal.net
*/
public function UTMLetterDesignator()
{	
if((84 >= $this->lat) && ($this->lat >= 72)) $LetterDesignator = 'X';
else if((72 > $this->lat) && ($this->lat >= 64)) $LetterDesignator = 'W';
else if((64 > $this->lat) && ($this->lat >= 56)) $LetterDesignator = 'V';
else if((56 > $this->lat) && ($this->lat >= 48)) $LetterDesignator = 'U';
else if((48 > $this->lat) && ($this->lat >= 40)) $LetterDesignator = 'T';
else if((40 > $this->lat) && ($this->lat >= 32)) $LetterDesignator = 'S';
else if((32 > $this->lat) && ($this->lat >= 24)) $LetterDesignator = 'R';
else if((24 > $this->lat) && ($this->lat >= 16)) $LetterDesignator = 'Q';
else if((16 > $this->lat) && ($this->lat >= 8)) $LetterDesignator = 'P';
else if(( 8 > $this->lat) && ($this->lat >= 0)) $LetterDesignator = 'N';
else if(( 0 > $this->lat) && ($this->lat >= -8)) $LetterDesignator = 'M';
else if((-8 > $this->lat) && ($this->lat >= -16)) $LetterDesignator = 'L';
else if((-16 > $this->lat) && ($this->lat >= -24)) $LetterDesignator = 'K';
else if((-24 > $this->lat) && ($this->lat >= -32)) $LetterDesignator = 'J';
else if((-32 > $this->lat) && ($this->lat >= -40)) $LetterDesignator = 'H';
else if((-40 > $this->lat) && ($this->lat >= -48)) $LetterDesignator = 'G';
else if((-48 > $this->lat) && ($this->lat >= -56)) $LetterDesignator = 'F';
else if((-56 > $this->lat) && ($this->lat >= -64)) $LetterDesignator = 'E';
else if((-64 > $this->lat) && ($this->lat >= -72)) $LetterDesignator = 'D';
else if((-72 > $this->lat) && ($this->lat >= -80)) $LetterDesignator = 'C';
else $LetterDesignator = 'Z'; //This is here as an error flag to show that the Latitude is outside the UTM limits
 
return($LetterDesignator);
}
 
/**
* Convert UTM to Longitude/Latitude
*
* Equations from USGS Bulletin 1532
* East Longitudes are positive, West longitudes are negative.
* North latitudes are positive, South latitudes are negative
* Lat and Long are in decimal degrees.
* Written by Chuck Gantz- chuck.gantz@globalstar.com, converted to PHP by
* Brenor Brophy, brenor@sbcglobal.net
*
* If a value is passed for $LongOrigin the the function assumes that
* a Local (to the Longitude of Origin passed in) Transverse Mercator
* coordinates is to be converted - not a UTM coordinate. This is the
* complementary function to the previous one. The function cannot
* tell if a set of LOCALNorthing/Easting coordinates are in the North
* or South hemesphere - they just give distance from the equator not
* direction - so only northern hemesphere lat/long coordinates are returned.
* If you live south of the equator there is a note later in the code
* explaining how to have it just return southern hemesphere lat/longs.
*
* @param float $LongOrigin
*/
public function convertTMtoLL($LongOrigin=null)
{
$k0 = 0.9996;
$e1 = (1-sqrt(1-$this->e2))/(1+sqrt(1-$this->e2));
$falseEasting = 0.0;
$y = $this->utmNorthing;
 
if (!$LongOrigin) { // It is a UTM coordinate we want to convert
sscanf($this->utmZone,"%d%s",$ZoneNumber,$ZoneLetter);
if($ZoneLetter >= 'N') {
$NorthernHemisphere = 1;//point is in northern hemisphere
} else {
$NorthernHemisphere = 0;//point is in southern hemisphere
$y -= 10000000.0;//remove 10,000,000 meter offset used for southern hemisphere
}
$LongOrigin = ($ZoneNumber - 1)*6 - 180 + 3; //+3 puts origin in middle of zone
$falseEasting = 500000.0;
}
 
// $y -= 10000000.0; // Uncomment line to make LOCAL coordinates return southern hemesphere Lat/Long
$x = $this->utmEasting - $falseEasting; //remove 500,000 meter offset for longitude
 
$eccPrimeSquared = ($this->e2)/(1-$this->e2);
 
$M = $y / $k0;
$mu = $M/($this->a*(1-$this->e2/4-3*$this->e2*$this->e2/64-5*$this->e2*$this->e2*$this->e2/256));
 
$phi1Rad = $mu	+ (3*$e1/2-27*$e1*$e1*$e1/32)*sin(2*$mu)
+ (21*$e1*$e1/16-55*$e1*$e1*$e1*$e1/32)*sin(4*$mu)
+(151*$e1*$e1*$e1/96)*sin(6*$mu);
$phi1 = rad2deg($phi1Rad);
 
$N1 = $this->a/sqrt(1-$this->e2*sin($phi1Rad)*sin($phi1Rad));
$T1 = tan($phi1Rad)*tan($phi1Rad);
$C1 = $eccPrimeSquared*cos($phi1Rad)*cos($phi1Rad);
$R1 = $this->a*(1-$this->e2)/pow(1-$this->e2*sin($phi1Rad)*sin($phi1Rad), 1.5);
$D = $x/($N1*$k0);
 
$tlat = $phi1Rad - ($N1*tan($phi1Rad)/$R1)*($D*$D/2-(5+3*$T1+10*$C1-4*$C1*$C1-9*$eccPrimeSquared)*$D*$D*$D*$D/24
+(61+90*$T1+298*$C1+45*$T1*$T1-252*$eccPrimeSquared-3*$C1*$C1)*$D*$D*$D*$D*$D*$D/720);
$this->lat = rad2deg($tlat);
 
$tlong = ($D-(1+2*$T1+$C1)*$D*$D*$D/6+(5-2*$C1+28*$T1-3*$C1*$C1+8*$eccPrimeSquared+24*$T1*$T1)
*$D*$D*$D*$D*$D/120)/cos($phi1Rad);
$this->long = $LongOrigin + rad2deg($tlong);
}
 
/**
* Configure a Lambert Conic Conformal Projection
*
* falseEasting & falseNorthing are just an offset in meters added to the final
* coordinate calculated.
*
* longOfOrigin & LatOfOrigin are the "center" latitiude and longitude of the
* area being projected. All coordinates will be calculated in meters relative
* to this point on the earth.
*
* firstStdParallel & secondStdParallel are the two lines of longitude (that
* is they run east-west) that define where the "cone" intersects the earth.
* Simply put they should bracket the area being projected.
*
* google is your friend to find out more
*
* @param integer $falseEasting
* @param integer $falseNorthing
* @param float $longOfOrigin
* @param float $latOfOrigin
* @param float $firstStdParallel
* @param float $secondStdParallel
*/
public function configLambertProjection ($falseEasting, $falseNorthing, $longOfOrigin, $latOfOrigin, $firstStdParallel, $secondStdParallel)
{
$this->falseEasting = $falseEasting;
$this->falseNorthing = $falseNorthing;
$this->longOfOrigin = $longOfOrigin;
$this->latOfOrigin = $latOfOrigin;
$this->firstStdParallel = $firstStdParallel;
$this->secondStdParallel = $secondStdParallel;
}
 
/**
* Convert Longitude/Latitude to Lambert Conic Easting/Northing
*
* This routine will convert a Latitude/Longitude coordinate to an Northing/
* Easting coordinate on a Lambert Conic Projection. The configLambertProjection()
* function should have been called prior to this one to setup the specific
* parameters for the projection. The Northing/Easting parameters calculated are
* in meters (because the datum used is in meters) and are relative to the
* falseNorthing/falseEasting coordinate. Which in turn is relative to the
* Lat/Long of origin The formula were obtained from URL:
* http://www.ihsenergy.com/epsg/guid7_2.html.
* Code was written by Brenor Brophy, brenor@sbcglobal.net
*
*/
public function convertLLtoLCC()
{
$e = sqrt($this->e2);
 
$phi = deg2rad($this->lat);	// Latitude to convert
$phi1	= deg2rad($this->firstStdParallel);	// Latitude of 1st std parallel
$phi2	= deg2rad($this->secondStdParallel);	// Latitude of 2nd std parallel
$lamda	= deg2rad($this->long);	// Lonitude to convert
$phio	= deg2rad($this->latOfOrigin);	// Latitude of Origin
$lamdao	= deg2rad($this->longOfOrigin);	// Longitude of Origin
 
$m1 = cos($phi1) / sqrt(( 1 - $this->e2*sin($phi1)*sin($phi1)));
$m2 = cos($phi2) / sqrt(( 1 - $this->e2*sin($phi2)*sin($phi2)));
$t1 = tan((pi()/4)-($phi1/2)) / pow(( ( 1 - $e*sin($phi1) ) / ( 1 + $e*sin($phi1) )),$e/2);
$t2 = tan((pi()/4)-($phi2/2)) / pow(( ( 1 - $e*sin($phi2) ) / ( 1 + $e*sin($phi2) )),$e/2);
$to = tan((pi()/4)-($phio/2)) / pow(( ( 1 - $e*sin($phio) ) / ( 1 + $e*sin($phio) )),$e/2);
$t = tan((pi()/4)-($phi /2)) / pow(( ( 1 - $e*sin($phi ) ) / ( 1 + $e*sin($phi ) )),$e/2);
$n	= (log($m1)-log($m2)) / (log($t1)-log($t2));
$F	= $m1/($n*pow($t1,$n));
$rf	= $this->a*$F*pow($to,$n);
$r	= $this->a*$F*pow($t,$n);
$theta = $n*($lamda - $lamdao);
 
$this->lccEasting = $this->falseEasting + $r*sin($theta);
$this->lccNorthing = $this->falseNorthing + $rf - $r*cos($theta);
}
/**
* Convert Easting/Northing on a Lambert Conic projection to Longitude/Latitude
*
* This routine will convert a Lambert Northing/Easting coordinate to an
* Latitude/Longitude coordinate. The configLambertProjection() function should
* have been called prior to this one to setup the specific parameters for the
* projection. The Northing/Easting parameters are in meters (because the datum
* used is in meters) and are relative to the falseNorthing/falseEasting
* coordinate. Which in turn is relative to the Lat/Long of origin The formula
* were obtained from URL http://www.ihsenergy.com/epsg/guid7_2.html. Code
* was written by Brenor Brophy, brenor@sbcglobal.net
*/
public function convertLCCtoLL()
{
$e = sqrt($e2);
 
$phi1	= deg2rad($this->firstStdParallel);	// Latitude of 1st std parallel
$phi2	= deg2rad($this->secondStdParallel);	// Latitude of 2nd std parallel
$phio	= deg2rad($this->latOfOrigin);	// Latitude of Origin
$lamdao	= deg2rad($this->longOfOrigin);	// Longitude of Origin
$E	= $this->lccEasting;
$N	= $this->lccNorthing;
$Ef	= $this->falseEasting;
$Nf	= $this->falseNorthing;
 
$m1 = cos($phi1) / sqrt(( 1 - $this->e2*sin($phi1)*sin($phi1)));
$m2 = cos($phi2) / sqrt(( 1 - $this->e2*sin($phi2)*sin($phi2)));
$t1 = tan((pi()/4)-($phi1/2)) / pow(( ( 1 - $e*sin($phi1) ) / ( 1 + $e*sin($phi1) )),$e/2);
$t2 = tan((pi()/4)-($phi2/2)) / pow(( ( 1 - $e*sin($phi2) ) / ( 1 + $e*sin($phi2) )),$e/2);
$to = tan((pi()/4)-($phio/2)) / pow(( ( 1 - $e*sin($phio) ) / ( 1 + $e*sin($phio) )),$e/2);
$n	= (log($m1)-log($m2)) / (log($t1)-log($t2));
$F	= $m1/($n*pow($t1,$n));
$rf	= $this->a*$F*pow($to,$n);
$r_	= sqrt( pow(($E-$Ef),2) + pow(($rf-($N-$Nf)),2) );
$t_	= pow($r_/($this->a*$F),(1/$n));
$theta_ = atan(($E-$Ef)/($rf-($N-$Nf)));
 
$lamda	= $theta_/$n + $lamdao;
$phi0	= (pi()/2) - 2*atan($t_);
$phi1	= (pi()/2) - 2*atan($t_*pow(((1-$e*sin($phi0))/(1+$e*sin(phi0))),$e/2));
$phi2	= (pi()/2) - 2*atan($t_*pow(((1-$e*sin($phi1))/(1+$e*sin(phi1))),$e/2));
$phi	= (pi()/2) - 2*atan($t_*pow(((1-$e*sin($phi2))/(1+$e*sin(phi2))),$e/2));
$this->lat = rad2deg($phi);
$this->long = rad2deg($lamda);
}
 
/**
* This is a useful function that returns the Great Circle distance from the gPoint to another Long/Lat coordinate
*
* Result is returned as meters
* @param float $lon1
* @param float $lat1
*/
public function distanceFrom($lon1, $lat1)
{
$lon2 = deg2rad($this->Long()); $lat2 = deg2rad($this->Lat());
$theta = $lon2 - $lon1;
$dist = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($theta));
 
// Alternative formula supposed to be more accurate for short distances
// $dist = 2*asin(sqrt( pow(sin(($lat1-$lat2)/2),2) + cos($lat1)*cos($lat2)*pow(sin(($lon1-$lon2)/2),2)));
return ( $dist * 6366710 ); // from http://williams.best.vwh.net/avform.htm#GCF
}
/**
* This function also calculates the distance between two points. In this case it just uses Pythagoras's theorm using TM coordinates.
* @param gPoint $pt
*/
public function distanceFromTM(&$pt)
{
$E1 = $pt->E(); $N1 = $pt->N();
$E2 = $this->E(); $N2 = $this->N();
$dist = sqrt(pow(($E1-$E2),2)+pow(($N1-$N2),2));
return $dist;
}
 
/**
* This function geo-references a gePoint to a given map. This means that it
* calculates the x,y pixel coordinate that coresponds to the Lat/Long value of
* the geoPoint. The calculation is done using the Transverse Mercator(TM)
* coordinates of the gPoint with respect to the TM coordinates of the center
* point of the map. So this only makes sense if you are using Local TM
* projection.
*
* $rX & $rY are the pixel coordinates that correspond to the Northing/Easting
* ($rE/$rN) coordinate it is to this coordinate that he point will be
* geo-referenced. The $LongOrigin is needed to make sure the Easting/Northing
* coordinates of the point are correctly converted.
*
* @param integer $rX
* @param integer $rY
* @param integer $rE
* @param integer $rN
* @param integer $Scale
* @param float $LongOrigin
*/
public function gRef($rX, $rY, $rE, $rN, $Scale, $LongOrigin)
{
$this->convertLLtoTM($LongOrigin);
$x = (($this->E() - $rE) / $Scale)	// The easting in meters times the scale to get pixels
// is relative to the center of the image so adjust to
+ ($rX);	// the left coordinate.
$y = $rY - // Adjust to bottom coordinate.
(($rN - $this->N()) / $Scale);	// The northing in meters
// relative to the equator. Subtract center point northing
// to get relative to image center and convert meters to pixels
$this->setXY((int)$x,(int)$y);	// Save the geo-referenced result.
}
}
/*
gpointconverter.class.php
PHP
*/

 
/**
* PHP class to convert Latitude+Longitude coordinates into UTM and wise versa.
*
* Code for datum and UTM conversion was converted from C++ code written by Chuck Gantz (chuck.gantz@globalstar.com) from http://www.gpsy.com/gpsinfo/geotoutm/
* The C++ code was refactored and rewritten into PHP code by Hans Duedal (hd@onlinecity.dk).
* The PHP conversion was inspired by work done by Brenor Brophy (brenor@sbcglobal.net), but derived from the "original" C++ source.
*
* @author chuck.gantz@globalstar.com, hd@onlinecity.dk
*
* GpointConverter (conversion between geographic points) Copyright (C) 2011 Hans Duedal (hd@onlinecity.dk)
*
* This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as
* published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
*
* This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
*
* This license can be read at: http://www.opensource.org/licenses/lgpl-2.1.php
*
* @link http://www.gpsy.com/gpsinfo/geotoutm/
* @link https://gist.github.com/840476
*/
class GpointConverter
{
const K0 = 0.9996;
/**
* Equatorial Radius
* @var integer
*/
private $a;
/**
* Square of eccentricity
* @var float
*/
private $eccSquared;
public function __construct($datumName='ETRS89')
{
$this->setEllipsoid($datumName);
$this->datum = $datumName;
}
 
/**
* Convert latitude/longitude into UTM coordinates. Equations from USGS Bulletin 1532
* Automatically calculates the zone, with special zone rules added for Denmark and Svalbard.
* Denmark stretches the zone 32 in ETRS89 to include all of Zealand so users don't have to deal with zone crossings.
*
* @param float $latitude
* @param float $longitude
*/
public function convertLatLngToUtm($latitude, $longitude)
{
//Make sure the longitude is between -180.00 .. 179.9
$LongTemp = ($longitude+180)-(int) (($longitude+180)/360)*360-180; // -180.00 .. 179.9;
$LatRad = deg2rad($latitude);
$LongRad = deg2rad($LongTemp);
if ($LongTemp >= 8 && $LongTemp <= 13 && $latitude > 54.5 && $latitude < 58) { // Special zones for Denmark: http://www.kms.dk/Referencenet/Referencesystemer/UTM_ETRS89/
$ZoneNumber = 32;
} else if( $latitude >= 56.0 && $latitude < 64.0 && $LongTemp >= 3.0 && $LongTemp < 12.0 ) { // From C++ code
$ZoneNumber = 32;
} else {
$ZoneNumber = (int) (($LongTemp + 180)/6) + 1;
// Special zones for Svalbard
if( $latitude >= 72.0 && $latitude < 84.0 ) {
if($LongTemp >= 0.0 && $LongTemp < 9.0) {
$ZoneNumber = 31;
} else if($LongTemp >= 9.0 && $LongTemp < 21.0) {
$ZoneNumber = 33;
} else if($LongTemp >= 21.0 && $LongTemp < 33.0) {
$ZoneNumber = 35;
} else if($LongTemp >= 33.0 && $LongTemp < 42.0) {
$ZoneNumber = 37;
}
}
}
$LongOrigin = ($ZoneNumber - 1)*6 - 180 + 3; //+3 puts origin in middle of zone
$LongOriginRad = deg2rad($LongOrigin);
$UTMZone = $ZoneNumber.self::getUtmLetterDesignator($latitude);
$eccPrimeSquared = ($this->eccSquared)/(1-$this->eccSquared);
$N = $this->a/sqrt(1-$this->eccSquared*sin($LatRad)*sin($LatRad));
$T = tan($LatRad)*tan($LatRad);
$C = $eccPrimeSquared*cos($LatRad)*cos($LatRad);
$A = cos($LatRad)*($LongRad-$LongOriginRad);
$M = $this->a*((1	- $this->eccSquared/4	- 3*$this->eccSquared*$this->eccSquared/64	- 5*$this->eccSquared*$this->eccSquared*$this->eccSquared/256)*$LatRad
- (3*$this->eccSquared/8	+ 3*$this->eccSquared*$this->eccSquared/32	+ 45*$this->eccSquared*$this->eccSquared*$this->eccSquared/1024)*sin(2*$LatRad)
+ (15*$this->eccSquared*$this->eccSquared/256 + 45*$this->eccSquared*$this->eccSquared*$this->eccSquared/1024)*sin(4*$LatRad)
- (35*$this->eccSquared*$this->eccSquared*$this->eccSquared/3072)*sin(6*$LatRad));
$UTMEasting = (float)(self::K0*$N*($A+(1-$T+$C)*$A*$A*$A/6
+ (5-18*$T+$T*$T+72*$C-58*$eccPrimeSquared)*$A*$A*$A*$A*$A/120)
+ 500000.0);
$UTMNorthing = (float)(self::K0*($M+$N*tan($LatRad)*($A*$A/2+(5-$T+9*$C+4*$C*$C)*$A*$A*$A*$A/24
+ (61-58*$T+$T*$T+600*$C-330*$eccPrimeSquared)*$A*$A*$A*$A*$A*$A/720)));
if($latitude < 0)	$UTMNorthing += 10000000.0; //10000000 meter offset for southern hemisphere
// Round them off, it's normally specified as integers and conversion is not terribly exact anyway
$UTMNorthing = (int) round($UTMNorthing);
$UTMEasting = (int) round($UTMEasting);
 
return array($UTMEasting,$UTMNorthing,$UTMZone);
}
/**
* Convert UTM to Longitude/Latitude
*
* Equations from USGS Bulletin 1532.
* East Longitudes are positive, West longitudes are negative.
* North latitudes are positive, South latitudes are negative
* Lat and Long are in decimal degrees.
*
* @param integer $UTMEasting
* @param integer $UTMNorthing
* @param string $UTMZone
*/
public function convertUtmToLatLng($UTMEasting, $UTMNorthing, $UTMZone)
{
$e1 = (1-sqrt(1-$this->eccSquared))/(1+sqrt(1-$this->eccSquared));
$x = $UTMEasting - 500000.0; //remove 500,000 meter offset for longitude
$y = $UTMNorthing;
sscanf($UTMZone,"%d%s",$ZoneNumber,$ZoneLetter);
if (strcmp('N',$ZoneLetter) <= 0) {
$NorthernHemisphere = 1;//point is in northern hemisphere
} else {
$NorthernHemisphere = 0;//point is in southern hemisphere
$y -= 10000000.0;//remove 10,000,000 meter offset used for southern hemisphere
}
$LongOrigin = ($ZoneNumber - 1)*6 - 180 + 3; //+3 puts origin in middle of zone
$eccPrimeSquared = ($this->eccSquared)/(1-$this->eccSquared);
$M = $y / self::K0;
$mu = $M/($this->a*(1-$this->eccSquared/4-3*$this->eccSquared*$this->eccSquared/64-5*$this->eccSquared*$this->eccSquared*$this->eccSquared/256));
$phi1Rad = $mu	+ (3*$e1/2-27*$e1*$e1*$e1/32)*sin(2*$mu)
+ (21*$e1*$e1/16-55*$e1*$e1*$e1*$e1/32)*sin(4*$mu)
+(151*$e1*$e1*$e1/96)*sin(6*$mu);
$phi1 = rad2deg($phi1Rad);
$N1 = $this->a/sqrt(1-$this->eccSquared*sin($phi1Rad)*sin($phi1Rad));
$T1 = tan($phi1Rad)*tan($phi1Rad);
$C1 = $eccPrimeSquared*cos($phi1Rad)*cos($phi1Rad);
$R1 = $this->a*(1-$this->eccSquared)/pow(1-$this->eccSquared*sin($phi1Rad)*sin($phi1Rad), 1.5);
$D = $x/($N1*self::K0);
$Lat = $phi1Rad - ($N1*tan($phi1Rad)/$R1)*($D*$D/2-(5+3*$T1+10*$C1-4*$C1*$C1-9*$eccPrimeSquared)*$D*$D*$D*$D/24
+(61+90*$T1+298*$C1+45*$T1*$T1-252*$eccPrimeSquared-3*$C1*$C1)*$D*$D*$D*$D*$D*$D/720);
$Lat = rad2deg($Lat);
$Long = ($D-(1+2*$T1+$C1)*$D*$D*$D/6+(5-2*$C1+28*$T1-3*$C1*$C1+8*$eccPrimeSquared+24*$T1*$T1)
*$D*$D*$D*$D*$D/120)/cos($phi1Rad);
$Long = $LongOrigin + rad2deg($Long);
return array($Lat,$Long);
}
 
/**
* Reference ellipsoids derived from Peter H. Dana's website:
* http://www.utexas.edu/depts/grg/gcraft/notes/datum/elist.html
* Department of Geography, University of Texas at Austin
* Internet: pdana@mail.utexas.edu 3/22/95
* Source:
* Defense Mapping Agency. 1987b. DMA Technical Report: Supplement to Department of Defense World Geodetic System 1984 Technical Report. Part I and II.
* Washington, DC: Defense Mapping Agency
* Alternative names added in for easy compatibility by hd@onlinecity.dk
*
* @param string $name
*/
public function setEllipsoid($name)
{
switch ($name) {
case 'Airy': $this->a = 6377563;$this->eccSquared = 0.00667054;break;
case 'Australian National': $this->a = 6378160;$this->eccSquared = 0.006694542;break;
case 'Bessel 1841': $this->a = 6377397;$this->eccSquared = 0.006674372;break;
case 'Bessel 1841 Nambia': $this->a = 6377484;$this->eccSquared = 0.006674372;break;
case 'Clarke 1866': $this->a = 6378206;$this->eccSquared = 0.006768658;break;
case 'Clarke 1880': $this->a = 6378249;$this->eccSquared = 0.006803511;break;
case 'Everest': $this->a = 6377276;$this->eccSquared = 0.006637847;break;
case 'Fischer 1960 Mercury': $this->a = 6378166;$this->eccSquared = 0.006693422;break;
case 'Fischer 1968': $this->a = 6378150;$this->eccSquared = 0.006693422;break;
case 'GRS 1967': $this->a = 6378160;$this->eccSquared = 0.006694605;break;
case 'GRS 1980': $this->a = 6378137;$this->eccSquared = 0.00669438;break;
case 'Helmert 1906': $this->a = 6378200;$this->eccSquared = 0.006693422;break;
case 'Hough': $this->a = 6378270;$this->eccSquared = 0.00672267;break;
case 'International': $this->a = 6378388;$this->eccSquared = 0.00672267;break;
case 'Krassovsky': $this->a = 6378245;$this->eccSquared = 0.006693422;break;
case 'Modified Airy': $this->a = 6377340;$this->eccSquared = 0.00667054;break;
case 'Modified Everest': $this->a = 6377304;$this->eccSquared = 0.006637847;break;
case 'Modified Fischer 1960': $this->a = 6378155;$this->eccSquared = 0.006693422;break;
case 'South American 1969': $this->a = 6378160;$this->eccSquared = 0.006694542;break;
case 'WGS 60': $this->a = 6378165;$this->eccSquared = 0.006693422;break;
case 'WGS 66': $this->a = 6378145;$this->eccSquared = 0.006694542;break;
case 'WGS 72': $this->a = 6378135;$this->eccSquared = 0.006694318;break;
case 'ED50': $this->a = 6378388;$this->eccSquared = 0.00672267;break; // International Ellipsoid
case 'WGS 84':
case 'EUREF89': // Max deviation from WGS 84 is 40 cm/km see http://ocq.dk/euref89 (in danish)
case 'ETRS89': // Same as EUREF89
$this->a = 6378137;
$this->eccSquared = 0.00669438;
break;
default:
//throw new \InvalidArgumentException('No ecclipsoid data associated with unknown datum: '.$name);
}
}
/**
* Get the UTM letter designator for a given latitude.
* returns 'Z' if latitude is outside the UTM limits of 84N to 80S
*
* @param float $latitude
*/
public static function getUtmLetterDesignator($latitude)
{
switch ($latitude) {
case ((84 >= $latitude) && ($latitude >= 72)): return 'X';
case ((72 > $latitude) && ($latitude >= 64)): return 'W';
case ((64 > $latitude) && ($latitude >= 56)): return 'V';
case ((56 > $latitude) && ($latitude >= 48)): return 'U';
case ((48 > $latitude) && ($latitude >= 40)): return 'T';
case ((40 > $latitude) && ($latitude >= 32)): return 'S';
case ((32 > $latitude) && ($latitude >= 24)): return 'R';
case ((24 > $latitude) && ($latitude >= 16)): return 'Q';
case ((16 > $latitude) && ($latitude >= 8)): return 'P';
case (( 8 > $latitude) && ($latitude >= 0)): return 'N';
case (( 0 > $latitude) && ($latitude >= -8)): return 'M';
case ((-8 > $latitude) && ($latitude >= -16)): return 'L';
case ((-16 > $latitude) && ($latitude >= -24)): return 'K';
case ((-24 > $latitude) && ($latitude >= -32)): return 'J';
case ((-32 > $latitude) && ($latitude >= -40)): return 'H';
case ((-40 > $latitude) && ($latitude >= -48)): return 'G';
case ((-48 > $latitude) && ($latitude >= -56)): return 'F';
case ((-56 > $latitude) && ($latitude >= -64)): return 'E';
case ((-64 > $latitude) && ($latitude >= -72)): return 'D';
case ((-72 > $latitude) && ($latitude >= -80)): return 'C';
default: return 'Z';	
}
}
}