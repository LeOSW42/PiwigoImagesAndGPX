# PiwigoImagesAndGPX

Create an map which can display one GPX file and/or Images from a piwigo category

# Installation

Put the `imagesInCat.php` file and `iFrameGPX` folder in your piwigo folder.
Add GPX files in the `iFrameGPX/GPS/` folder.

You will need to install piwigo-openstreetmap plugin, and geolocate some photos.

You will have to change the IGN API key if you want to use IGN baselayer

# Usage

## Parameters

When asking the `iFrameGPX/index.php` file, you can pass some **GET** attributes :

 * `dark`, if set, will use dark theme. Default = clear
 * `scrollwheel`, if set, will enable scroll by mouse wheel on the map. Default = disable
 * `basemap`, is the base map layer, can be : *osmfr*, *outdoor*, *outdoora*, *ostopo*, *ign*.
 * `catID`, is the ID of a Piwigo category where to take photos, display photos with correct permissions. By default no photos
 * `gpxID`, is the exact filename of the gpx file (without .gpx) which is on `iFrameGPX/GPS/` folder. will display nothing if the file doesn't exists.

## Example of usage

Here is the code to insert using iframe, enabling HTML5 fullscreen API :

    <iframe src="http://leo.lstronic.com/piwigo/iFrameGPX/?dark=1&basemap=outdoor&gpxID=HrpJ37&catID=12" marginheight="0" marginwidth="0" frameborder="0" height="600" scrolling="no" width="100%" allowfullscreen></iframe>
