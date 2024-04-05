(function (Drupal, once) {
  Drupal.behaviors.storesMap = {
    attach: function (context, settings) {
      once('storesMap', '.leaflet__map', context).forEach(function (element) {
        const id = element.getAttribute('data-view-id');

        const options = settings[id].stores;
        const color = options.color;
        const size = options.size;
        const zoom = options.zoom;
        const locations = options.locations;
        element.style.height = '400px';
        const map = L.map(element).setView([51.505, -0.09], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 20,
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        locations.forEach(function(location) {
          const lat = location[0].lat;
          const lon = location[0].lon;

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
