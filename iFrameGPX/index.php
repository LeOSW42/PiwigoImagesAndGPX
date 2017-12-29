<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<title>Carte</title>
		<link rel="stylesheet" href="leaflet/leaflet.css" />
		<script src="leaflet/leaflet.js"></script>
		<link rel="stylesheet" href="leaflet-wheelscroll/L.Control.MouseScroll.css" />
		<script src="leaflet-wheelscroll/L.Control.MouseScroll.js"></script>
		<link rel="stylesheet" href="leaflet-fullscreen/Control.FullScreen.css" />
		<script src="leaflet-fullscreen/Control.FullScreen.js"></script>
		<script src="leaflet-elevation/d3.v3.min.js" charset="utf-8"></script>
		<link rel="stylesheet" href="leaflet-elevation/Leaflet.Elevation-0.0.2.css" />
		<script src="leaflet-elevation/Leaflet.Elevation-0.0.2.min.js"></script>
		<link rel="stylesheet" href="leaflet-cluster/MarkerCluster.css" />
		<script src="leaflet-cluster/leaflet.markercluster.js"></script>
		<link rel="stylesheet" href="leaflet-photo/Leaflet.Photo.css" />
		<script src="leaflet-photo/Leaflet.Photo.js"></script>
		<script src="leaflet-photo/reqwest.min.js"></script>
		<script src="leaflet-gpx/gpx.js"></script>
		<style media="screen" type="text/css">
			* { margin: 0; padding: 0; }
			body, html, #map { width: 100%; height: 100%; }
			#elevation, .background { margin: -1px !important; height: 100px !important;}
			#elevation { background: transparent !important; border: none !important;}
		</style>
	</head>
	<body>
		<div id="map" <?php if ($_GET['dark']) echo "class='dark'"; ?>></div>

<script type="text/javascript">

// ******** Generating the Leaflet map ********

// IGN URL to the IGN layer
var KeyIGN = "ataevimogohmg1wxpg1jo2wh" // professionels.ign.fr

var url_wmts_ign =  "//wxs.ign.fr/"+ 
    KeyIGN + 
    "/geoportail/wmts?LAYER="+
    "GEOGRAPHICALGRIDSYSTEMS.MAPS"+
    "&EXCEPTIONS=text/xml&FORMAT="+
    "image/jpeg"+
    "&SERVICE=WMTS&VERSION=1.0.0&REQUEST=GetTile&STYLE="+
    "normal"+
    "&TILEMATRIXSET=PM&&TILEMATRIX={z}&TILECOL={x}&TILEROW={y}"; // Correct tile

// Differents layers for the map
var	osmfr   = L.tileLayer('//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {maxZoom: 20, attribution: 'Maps © <a href="http://www.openstreetmap.fr">OpenSreetMap France</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});
    outdoor  = L.tileLayer('//{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png', {maxZoom: 18, attribution: 'Maps © <a href="http://www.thunderforest.com">Thunderforest</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'}),
    outdoora  = L.tileLayer('//s1.outdooractive.com/osm/OSMSummer/{z}/{x}/{y}.png', {maxZoom: 18, attribution: 'Maps © <a href="http://www.outdooractive.com">Outdoor Active</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'}),
    ostopo  = L.tileLayer('//{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {maxZoom: 16, attribution: 'Maps © <a href="http://opentopomap.org">OpenTopoMap</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'}),
    ign  = L.tileLayer(url_wmts_ign, {maxZoom: 18, attribution: 'Maps & Data © <a href="http://www.ign.fr/">IGN</a>'});

// Creation of the map
var map = L.map('map', {
  layers: [<?php echo $_GET['basemap']; ?>],
<?php if (!$_GET['scrollwheel']) echo "scrollWheelZoom: false,"; ?>
  fullscreenControl: true, // Fullscreen button
  attributionControl: false,
  fullscreenControlOptions: {
    position: 'topleft'
  }}).setView([47, 2], 6); // Hole france

var attributionControl = map.addControl(new L.control.attribution({position: 'topright'})); 

// Mouse Scroll API
map.addControl(new L.Control.MouseScroll());

// Base layers
var baseLayers = {
	"OSM France": osmfr,
	"OSM Outdoor": outdoor,
	"IGN France": ign,
	"OSM Topo": ostopo,
	"OSM OutdoorActive ": outdoora
};
L.control.layers(baseLayers).addTo(map);


// Photos
<?php if (isset($_GET['catID']) and $_GET['catID'] != "" and is_numeric($_GET['catID'])) { ?>
var photoLayer = L.photo.cluster({ spiderfyDistanceMultiplier: 1.2 }).on('click', function (evt) {
	evt.layer.bindPopup(L.Util.template('<a href="{url}"><img src="{photo}"/></a><p style="margin: 0 !important;">{caption} <span style="display:inline-block; width: 100%; font-size: 0.8em; white-space: nowrap; overflow:hidden !important; text-overflow: ellipsis;">{comment}</span></p>', evt.layer.photo), {
		className: 'leaflet-popup-photo',
		minWidth: 460
	}).openPopup();
});


reqwest({
	url: '../imagesInCat.php?catID=<?php echo $_GET['catID']; ?>',
	type: 'jsonp',
	success: function (data) {
		var photos = [];

		for (var i = 0; i < data.length; i++) {
			var photo = data[i];
			if (photo[0]) {
				photos.push({
					lat: photo[0],
					lng: photo[1],
					url: photo[4],
					photo: photo[5],
					caption: photo[2],
					comment: photo[6],
					thumbnail: photo[3]
				});
			}	
		}

		photoLayer.add(photos).addTo(map);
		map.fitBounds(photoLayer.getBounds());
	}
});
<?php } ?>


// GPX track and elevation
<?php if (file_exists("./GPS/".$_GET['gpxID'].".gpx")) { ?>
var el = L.control.elevation({
    theme: "dark-theme", //default: lime-theme
    position: "bottomleft",
    width: document.getElementById('map').offsetWidth+3,
    height: 102,
    margins: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
    },
    useHeightIndicator: true, //if false a marker is drawn at map position
    interpolation: "linear", //see https://github.com/mbostock/d3/wiki/SVG-Shapes#wiki-area_interpolate
    hoverNumber: {
        decimalsX: 3, //decimals on distance (always in km)
        decimalsY: 0, //deciamls on height (always in m)
        formatter: undefined //custom formatter function may be injected
    },
    xTicks: undefined, //number of ticks in x axis, calculated by default according to width
    yTicks: undefined, //number of ticks on y axis, calculated by default according to height
    collapsed: false    //collapsed mode, show chart on click or mouseover
});
el.addTo(map);
var gpx = './GPS/<?php echo $_GET['gpxID']; ?>.gpx'; // URL to your GPX file or the GPX itself
var g = new L.GPX(gpx, {
	async: true,
	marker_options: {
		startIconUrl: './leaflet-gpx/start.png',
		endIconUrl: './leaflet-gpx/finish.png',
		wptIconUrls : {
			'night': './leaflet-gpx/night.png',
		},
		shadowUrl: '',
		iconSize: [32, 37],
		iconAnchor: [16, 37],
		clickable: false
	}
});
g.on('loaded', function(e) {
	map.fitBounds(e.target.getBounds());
	map.zoomOut(1);
});
g.on("addline",function(e){
	el.addData(e.line);
});
g.addTo(map);

map.on('resize',function(e){
	var elevationdiv = document.getElementById("elevation");
	elevationdiv.parentNode.removeChild(elevationdiv);
	el = L.control.elevation({
	    theme: "dark-theme", //default: lime-theme
	    position: "bottomleft",
	    width: document.getElementById('map').offsetWidth+3,
	    height: 102,
	    margins: {
	        top: 0,
	        right: 0,
	        bottom: 0,
	        left: 0
	    },
	    useHeightIndicator: true, //if false a marker is drawn at map position
	    interpolation: "linear", //see https://github.com/mbostock/d3/wiki/SVG-Shapes#wiki-area_interpolate
	    hoverNumber: {
	        decimalsX: 3, //decimals on distance (always in km)
	        decimalsY: 0, //deciamls on height (always in m)
	        formatter: undefined //custom formatter function may be injected
	    },
	    xTicks: undefined, //number of ticks in x axis, calculated by default according to width
	    yTicks: undefined, //number of ticks on y axis, calculated by default according to height
	    collapsed: false    //collapsed mode, show chart on click or mouseover
	});
	el.addTo(map);
	g.reload();
	el.clear();
	g.on("addline",function(e){
		el.clear();
		el.addData(e.line);
	});
});

<?php } ?>


</script>

	</body>
</html>
