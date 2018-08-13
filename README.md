# Task 

To show [NOTAM](https://en.wikipedia.org/wiki/NOTAM) info (marker + info window) on google maps by entered airport [ICAO](https://en.wikipedia.org/wiki/International_Civil_Aviation_Organization) code

*Main classes are located at the folder:* **`/vendor/rrtest`**

# Features
* For some ICAO codes there are couple NOTAM with the same coordinates (for YMML for example). App displays them all in one window.
* For some ICAO codes (for KLAX fo example) there are many NOTAM with wrong ItemQ so app can not put notice to the map because app doesn't have coordinates.
* This app is a little reponsive ;-)

# Clarifications
* The documentation indicates that the App Access Key is expired after 5 hours. I tried and did not encounter it, but still, when it's been 5 hours since it was received, App sending a request for a new one.
* I did not use any php framework as I know that your system is based on your own framework
* I used [Guzzle](http://docs.guzzlephp.org/en/stable/) for http-requests and [Twig](https://twig.symfony.com/) as template engine because I started learning them and decided to use them in real project
* I used composer for installation Guzzle, Twig and classes autoloading
* Main classes are located at the folder: **`/vendor/rrtest`**