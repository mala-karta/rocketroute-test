var map;
var markers = [];

function sendRequest() {

    hideError();
    clearMapMarkers();
    var icaoValue = document.getElementById('icao').value;

    if (!isValidIcao(icaoValue)) {
        showError('Please enter valid ICAO code. ICAO code must consists of 4 letters');
        return false;
    }

    showPreloader();

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            processResponse(this);
            hidePreloader();
        }
    };
    xhr.open('POST', '');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send('icao=' + icaoValue);
}

function processResponse(xhr) {

    if (!xhr.responseText) {
        return false;
    }

    var obj = JSON.parse(xhr.responseText);
    if ('error' == obj.status) {
        showError(obj.message);
    }

    if ('ok' == obj.status) {
        showNotam(obj.notam);
    }

}

function clearMapMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
}

function showNotam(notam)
{
    notam.forEach(function(element){
        var position = {lat: element.lat, lng: element.lng};

        var icon = {
         url: "images/warning-icon-th.png", // url
         scaledSize: new google.maps.Size(25, 25), // scaled size
         origin: new google.maps.Point(0,0), // origin
         anchor: new google.maps.Point(0, 0) // anchor
         };

        var infowindow = new google.maps.InfoWindow({
            content: element.msg,
            maxWidth: 200
        });
        // The marker, positioned at Uluru
        var marker = new google.maps.Marker({position: position, map: map, icon: icon });
        marker.addListener('click', function() {
            infowindow.open(map, marker);
        });

        markers.push(marker);

    });

    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < markers.length; i++) {
        bounds.extend(markers[i].getPosition());
    }

    map.fitBounds(bounds);

}

function isValidIcao(icaoValue) {
    if (!icaoValue || 4 != icaoValue.length) {
        return false;
    }

    var pos = icaoValue.search(/^[A-Z]{4}$/i);
    if (0 != pos) {
        return false;
    }
    return true;
}

function showError(msgTxt) {
    document.getElementById('icaoError').innerHTML  = 'Error: ' + msgTxt;
}

function hideError() {
    document.getElementById('icaoError').innerHTML  = '';
}



// Initialize and add the map
function initMap() {
    // The map
    var mapOptions = {
        center: new google.maps.LatLng(49.7015981,25.7306198),
        zoom: 4.7,
        maxZoom: 15
    }

    map = new google.maps.Map(
        document.getElementById('map'), mapOptions  );

}

function checkKey(event) {
    //On 'Enter' - do not submit form, do not reload page - just send request and continue working
    if (13 == event.keyCode ) {
        sendRequest();
        return false;
    }
}

function showPreloader() {
    document.getElementById('preloader').style.display = 'block';
}

function hidePreloader() {
    document.getElementById('preloader').style.display = 'none';
}