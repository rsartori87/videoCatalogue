/**
 * Created by sonic on 12/07/16.
 */
'use strict';
(function() {
    var app = angular.module('application', ['ngScroll']);

    app.controller('SearchController', ['$http', function($http, $scope) {
        this.key = '';
        this.start = 0;
        this.immagini = [];
        var that = this;
        $http.post('/data', {'key': this.key, 'start': this.start}).success(function(response) {
            that.immagini = response.data;
        });
        this.send = function() {
            $http.post('/data', {'key': this.key, 'start': this.start}).success(function(response) {
                that.immagini = response.data;
            });
        };
        this.scroll = function(evento) {
            if (evento.directionY === "down") {
                var winheight = $(window).height();
                var docheight = $(document).height();
                var scrollTop = $(window).scrollTop();
                var trackLength = docheight - winheight;
                var pctScrolled = Math.floor(scrollTop / trackLength * 100);
                if (pctScrolled > 50) {
                    console.log(pctScrolled);
                    this.start += 40;
                    $http.post('/data', {'key': this.key, 'start': this.start}).success(function(response) {
                        //that.immagini.concat(data);
                        Array.prototype.push.apply(that.immagini, response.data);
                    });
                }
            }
        }
    }]);
})();