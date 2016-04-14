<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>CS174 Google Maps</title>
    <style>

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
            height: 100%;
        }
        .controls {
            margin-top: 10px;
            border: 1px solid transparent;
            border-radius: 2px 0 0 2px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            height: 32px;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        #pac-input {
            background-color: #fff;
            font-family: Roboto;
            font-size: 15px;
            font-weight: 300;
            margin-left: 12px;
            padding: 0 11px 0 13px;
            text-overflow: ellipsis;
            width: 300px;
        }

        #pac-input:focus {
            border-color: #4d90fe;
        }

        .pac-container {
            font-family: Roboto;
        }

        #type-selector {
            color: #fff;
            background-color: #4d90fe;
            padding: 5px 11px 0px 11px;
        }

        #type-selector label {
            font-family: Roboto;
            font-size: 13px;
            font-weight: 300;
        }

        #target {
            width: 345px;
        }

    </style>



    <?php

    $array_position;

    $array_container = null;

    //$age = $_GET['age'];

    // $radius = $_GET['radius'];

    $radius = $_COOKIE['rad'];

    $y = $_COOKIE['lat'];
    $x = $_COOKIE['long'];

    function connectToDB2() {
        echo "<br>hi this is database connecting ........";
        $database = "test";
        $user = "db2admin";
        $password = "9872476129Mm";

        $conn = db2_connect($database, $user, $password);

        if ($conn) {
            print "<br> connection succeed   ";
        }
        else {
            echo "<br>Connection failed.";
        }

        return $conn;
    }
    function insertValues($sql, $conn) {

        try {
            $result = db2_exec($conn, $sql);

        }catch (Exception $e){
            print "<br>$e";
        }
        // print"$result";

        //db2_close($conn);
        return $result;
    }
    ;
    function get_array_table($sql) {

        $result = insertValues($sql, connectToDB2());

        // $result = db2_prepare(connectToDB2(),$sql);

        $row = db2_fetch_array($result);

        db2_close(connectToDB2());
        // echo $row[0];


        return $row;
    }

    function getarray_restaurant_in_radius($rad,$x,$y) {

        if($rad != null) {
            // echo $rad;
        }

        if($rad == null){
            $rad = 1;
        }

        if($x == null and $y == null) {

            $x = -122.0577870;
            $y = 36.9915854;
        }

        $database = "test";
        $user = "db2admin";
        $password = file_get_contents('password.txt');

        $conn = db2_connect($database, $user, $password);

        if ($conn) {
            // print "\nconnection succeed   ";
        } else {
            print "\nconnection Failed ------------- can't Database Access Database    ";
        }

        global $array_position, $array_container;


        $sql = "select name1, name2, street, city, state, zip, county , db2gse.ST_X(loc), db2gse.ST_Y(loc), loc From maninderpal.restaurant where db2gse.st_distance(db2gse.ST_POINt('$x','$y',1),loc, 'STATUTE MILE') < $rad";

        if ($conn) {

            $result = db2_exec($conn, $sql);

            global $array_position, $array_container;

            $array_position = array("","","","","","","","","","",);

            if($result) {
                while ($row = db2_fetch_array($result)) {

                    $array_position = array($row[0], $row[2], $row[3], $row[4], $row[5], $row[7], $row[8], $row[9]);

                    $array_container[] = $array_position;

                }
            }


            db2_close($conn);
        }

        return $array_container;
    }

    function getarray_school() {

        $database = "test";
        $user = "db2admin";
        $password = file_get_contents('password.txt');

        $conn = db2_connect($database, $user, $password);

        if ($conn) {
            // print "\nconnection succeed   ";
        } else {
             print "\nconnection Failed ------------- can't Database Access Database    ";
        }

        $sql = "SELECT * FROM maninderpal.school";

//$sql = "select name1, name2, street, city, state, zip, county, long, lat, loc From maninderpal.school where city = 'Santa Cruz'";

        if ($conn) {

            $result = db2_exec($conn, $sql);


            $array_position = array("","","","","","","","","","",);

            while ($row=db2_fetch_array($result))
            {

                global $array_position, $array_container;

                $array_position = array($row[0],$row[2],$row[3],$row[4],$row[5],$row[7],$row[8],$row[9]);

                $array_container[] = $array_position;

            }

            for ($i=0;$i<count($array_container);$i++){
                for ($j=0;$j<count($array_position);$j++) {
                    //echo $array_container[$i][$j] . "  ";
                }
                // echo "<br>";
            }

            //print $result;

            db2_close($conn);
        }

        return $array_container;
    }

    if($radius){
        $restaurant_array = getarray_restaurant_in_radius($radius,$x,$y);
    } else {
        $radius = 1;
        $restaurant_array = getarray_restaurant_in_radius($radius);
    }
    $school_array = getarray_School();

    ?>
</head>
<body>


<input id="pac-input" class="controls" type="text" placeholder="Search Box">
<div id="map"></div>


<script>


    function display_map() {

        var long = '-122.0577870'; // center map position
        var lat = '36.9915854';

        var k;
        var fullLen;
        var l;

        var a = <?php  echo json_encode($restaurant_array);?>;

        var b = <?php  echo json_encode($school_array);?>;

        var locations = [];

        if (a != null) {

            // window.alert(a);

            fullLen = a.length + b.length;

            for (k = 0; k < a.length; k++) {

                locations[k] = [a[k][0], a[k][1], a[k][2], a[k][3], a[k][4], a[k][5], a[k][6], a[k][7], k];

            }
        }

        k = 0;

        if (a == null) {

            fullLen = b.length;

            //  window.alert(fullLen);

            for (l = 0; l < fullLen; l++) {

                locations[l] = [b[k][0], b[k][1], b[k][2], b[k][3], b[k][4], b[k][5], b[k][6], b[k][7], k];

                k++;
            }
        } else {

            for (l = a.length; l < fullLen; l++) {

                locations[l] = [b[k][0], b[k][1], b[k][2], b[k][3], b[k][4], b[k][5], b[k][6], b[k][7], k];

                k++;
            }
        }

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: new google.maps.LatLng(lat, long),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });
        //-------------------------------------------------------------------------

        var markers = [];
        // [START region_getplaces]
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // Clear out the old markers.
            markers.forEach(function(marker) {
                marker.setMap(null);
            });
            markers = [];

            var info_window = new google.maps.InfoWindow();


            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    title: place.name,
                    position: place.geometry.location
                }));

                google.maps.event.addListener(markers[0], 'click', function() {

                    marker_position_lat = this.position.lat();

                    marker_position_long = this.position.lng();

                    info_window.setContent(place.name +" ( " + marker_position_lat + ", " + marker_position_long +  ")" + "<br>"+ contentString);

                    locations[locations.length] = ["", "", "", "", "", "", marker_position_lat, marker_position_long, locations.length];

                   // window.alert(locations[locations.length-1]);

                    info_window.open(map, markers[0]);
                });

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });

        //---------------------------------------------------------------------------


        var iconSrc = [];

        iconSrc['Starbucks'] = 'icon/coffee.png';
        iconSrc['Burger King'] = 'icon/azure.png';
        iconSrc['Taco Bell'] = 'icon/green.png';
        iconSrc['Jack In the Box'] = 'icon/blue.png';
        iconSrc['Jack in the Box'] = 'icon/blue.png';
        iconSrc['McDonalds'] = 'icon/mc.png';
        iconSrc['Wendys'] = 'icon/subway.png';
        iconSrc['Subway'] = 'icon/pink.png';


        iconSrc['monsbey college'] = 'icon/college.png';
        iconSrc['Bethany University'] = 'icon/college.png';
        iconSrc['Cabrillo College'] = 'icon/college.png';
        iconSrc['Merrill College'] = 'icon/college.png';
        iconSrc['UC Santa Cruz'] = 'icon/college.png';

        var i;

        var contentString = '<br><br><button onclick="one_mile()">1 mile</button><br><button onclick="two_mile()">2 mile</button><br> <button onclick="five_mile()">5 mile</button><br> <button onclick="show_all()">Show All</button>';

        var infowindow = new google.maps.InfoWindow();

        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][6], locations[i][5]),
                icon: iconSrc[locations[i][0]],
                animation: google.maps.Animation.DROP,
                map: map
            });

            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {

                    if (iconSrc[locations[i][0]] == 'icon/college.png') {
                        infowindow.setContent(locations[i][0] + "<br><br> " + locations[i][1] + ", " + locations[i][2] + ", " + locations[i][3] + "<br><br> ( " + locations[i][6] +",  " + locations[i][5] + " )"+ contentString);
                        marker_position_lat = this.position.lat();

                        marker_position_long = this.position.lng();

                    } else {
                        infowindow.setContent(locations[i][0] + "<br><br> " + locations[i][1] + ", " + locations[i][2] + ", " + locations[i][3]);
                    }

                    infowindow.open(map, marker);
                }
            })(marker, i));
        }

    }

    display_map();

    function one_mile() {

        one = 1;
        document.cookie = " rad =" + one;
        document.cookie = "lat =" + marker_position_lat;
        document.cookie = "long =" + marker_position_long;

        location.reload();
        //display_map();

    }

    function two_mile() {


        document.cookie = "rad = 2";

        document.cookie = "lat =" + marker_position_lat;
        document.cookie = "long =" + marker_position_long;

        location.reload();
        //  display_map();

    }

    function five_mile() {

        document.cookie = "rad = 5";

        document.cookie = "lat =" + marker_position_lat;
        document.cookie = "long =" + marker_position_long;
        //display_map();

        location.reload();
    }

    function show_all() {

            document.cookie = "rad = 50";

            document.cookie = "lat =" + marker_position_lat;
            document.cookie = "long =" + marker_position_long;
            //display_map();

            location.reload();


    }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNAwFysYVXJnuwcJih-85cipy-cEXUK9c&libraries=places&callback=display_map"
        async defer></script>

</body>
</html>