var dash_button = require('node-dash-button'),
    request = require('request'),
    urlJeedom = process.argv[2],
    debug = process.argv[5] || 1;

if (process.argv[4] == 0) {
    var conf = process.argv[3];
else {
    var conf = JSON.parse(process.argv[3]);
}

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";


var dash = dash_button(conf); //address from step above

dash.on("detected", function (dash_id){
  urlj = urlJeedom + "&uid=" + dash_id;
  if (debug == 1) {console.log("Calling Jeedom " + urlj);}
  request({
    url: urlj,
    method: 'PUT',
  },
  function (error, response, body) {
    if (!error && response.statusCode == 200) {
      if (debug == 1) {console.log((new Date()) + "Got response Value: " + response.statusCode);}
    }else{
      console.log((new Date()) + " - Error : "  + error );
    }
  });
});
