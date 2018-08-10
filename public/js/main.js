function sendRequest() {

    hideError();
    var icaoValue = document.getElementById('icao').value;

    if (!isValidIcao(icaoValue)) {
        showError('Please enter valid ICAO code. ICAO code must consists of 4 letters');
        return false;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send('icao=' + icaoValue);
    xhr.onload = function() {
        /*if (xhr.status === 200 && xhr.responseText !== newName) {
            alert('Something went wrong.  Name is now ' + xhr.responseText);
        }
        else if (xhr.status !== 200) {
            alert('Request failed.  Returned status of ' + xhr.status);
        }*/
    };





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
    // The location of Uluru
    var uluru = {lat: -25.344, lng: 131.036};
    // The map, centered at Uluru

    var mapOptions = {
        center: new google.maps.LatLng(20, 0),
        zoom: 2
    }

    var map = new google.maps.Map(
        document.getElementById('map'), mapOptions  );

    /*var icon = {
        url: '{{ markerImage }}', // url
        scaledSize: new google.maps.Size(25, 25), // scaled size
        origin: new google.maps.Point(0,0), // origin
        anchor: new google.maps.Point(0, 0) // anchor
    };*/
    // The marker, positioned at Uluru
//        var marker = new google.maps.Marker({position: uluru, map: map, icon: icon });
}