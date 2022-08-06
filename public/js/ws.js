(function() {

    var app = {
        ws: null,

        config: {
            uri: 'ws://127.0.0.1',
            port: window.WS_PORT,
        },

        init: () => {
            app.connectToServer();
            app.listenEvents();
        },

        listenEvents: () => {
            document.getElementById('message-form').addEventListener("submit", app.handleFormSubmit, false);
        },

        connectToServer: () => {
            var wsServer = app.config.uri + ':' + app.config.port;
            if (window.WS_TOKEN) {
                wsServer += '/?token=' + window.WS_TOKEN;
            }
            app.ws = new WebSocket(wsServer);

            app.ws.onopen = function (evt) {
                console.log("Connected to WebSocket server.");
            };

            app.ws.onclose = function (evt) {
                console.log("Disconnected");
            };

            app.ws.onmessage = function (evt) {
                console.log('Received data from server: ' + evt.data);
                app.handleIncomingMessage(evt.data);
            };

            app.ws.onerror = function (evt, e) {
                console.log('Error occured: ' + evt.data);
            };
        },

        /**
         * @param {Event} e
         */
        handleFormSubmit: (e) => {
            e.preventDefault();
            app.ws.send(document.getElementById('message-box').value);
        },

        /**
         * @param {String} data
         */
        handleIncomingMessage: (data) => {
            let parsedData = JSON.parse(data);
            app.addInputMessage(parsedData);
        },

        /**
         * @param {Object} parsedData
         */
        addInputMessage: (parsedData) => {
            let input = document.createElement("li");
            let user = document.createElement('strong');
            let message = document.createElement('span');

            user.innerText = parsedData.user;
            message.innerText = parsedData.message;
            input.append(user, message);

            document.getElementById('output').appendChild(input);
        },
    };

    app.init();

})();
