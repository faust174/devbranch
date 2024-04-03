(function (Drupal, once) {
  Drupal.behaviors.storesMap = {
    attach: function (context, settings) {
      once('storesMap', '#map', context).forEach(function () {

        let options = settings.stores;
        let color = options.color;
        let size = options.size;
        let zoom = options.zoom;
        let locations = options.locations;

        document.getElementById('map').style.height = '400px';
        let map = L.map('map').setView([51.505, -0.09], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 20,
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        locations.forEach(function(location) {
          let lat = location[0].lat;
          let lon = location[0].lon;

          L.circleMarker([lat, lon], {
            color: color,
            fillOpacity: 1,
            radius: size,
          }).addTo(map);

        });
      });
    }
  };

})(Drupal, once);
