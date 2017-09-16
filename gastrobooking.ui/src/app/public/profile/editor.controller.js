/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';



    angular
        .module('app.restaurant')
        .controller('EditorController', EditorController);
    /*@ngNoInject*/

    function EditorController($rootScope,ProfileService) {

        var data = [
            
            {
                name: 'menutype',
                params: {
                    'background-color': '#b3282e',
                    'color': 'white',
                    'font-family':'Ubuntu,sans-serif',
                    'font-size':'14px'
                },
                type: 1
            },
            {
                name: 'menugroup',
                params: {
                    'background-color': 'white',
                    'color': '#888',
                    'font-family':'Ubuntu,sans-serif',
                    'font-size':'14px'
                },
                type: 1
            },
            {
                name: 'menuofdaybutton',
                params: {
                    'background-color': '#b3282e',
                    'color': 'white',
                    'font-family':'sans-serif',
                    'font-size':'14px'
                },
                type: 1
            },
            {
                name: 'menusubgroup',
                params: {
                    'background-color': 'white',
                    'color': '#b3282e',
                    'font-family':'Ubuntu, sans-serif',
                    'font-size':'18px'
                },
                type: 1
            },
            {
                name: 'menutitle',
                params: {
                    'background-color': 'white',
                    'font-family':'Ubuntu, sans-serif',
                    'font-size':'14px'
                },
                type: 1
            },
            {
                name: 'menulist',
                params: {
                    'background-color': 'white',
                    'color': '#b3282e',
                    'font-family':'Ubuntu, sans-serif',
                    'font-size':'18px'


                },
                type: 1
            },
            {
                name: 'menulistcomment',
                params: {
                    'background-color': 'white',
                    'color': '#333',
                    'font-family':'Ubuntu,sans-serif',
                    'font-size':'14px'


                },
                type: 1
            },
            {
                name: 'bookbutton',
                params: {
                    'background-color': '#b6bf00',
                    'color': 'white',
                    'font-family':'sans-serif',
                    'font-size':'14px'

                },
                type: 1
            },
            {
                name: 'datepicker',
                params: {
                    'background-color': 'white',
                    'color': '#555',
                    'font-family':'sans-serif',
                    'font-size':'14px'

                },
                type: 1
            },
            {
                name: 'menulistorder',
                params: {
                    'background-color': 'white',
                    'color': '#333',
                    'font-family':'Ubuntu,sans-serif',
                    'font-size':'14px'


                },
                type: 1
            },
            {
                name: 'headerchange',
                params: {

                    'background-color': 'white',
                    'font-family':'Ubuntu,sans-serif',
                    'font-size':'14px'

                },
                type: 1
            },
            {
                name: 'headercolor',
                params: {
                    'color': '#777'
                },
                type: 2
            },
            {
                name: 'bgcolor',
                params: {
                    'background-color': 'white'
                },
                type: 2
            },
            {
                name: 'showphoto',
                params: {
                    'option': 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'photo_half_size',
                params: {
                    'option': 'on'
                },
                type: 4,
                defaults: ['on', 'off']
            },
            {
                name: 'listdetail',
                params: {
                    'option': 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'menusubgroup_activation',
                params: {
                    'option': 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'menulist_prefix_one',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'menulist_prefix_two',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'menulist_rectangle',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'show_order',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'dropwindow',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            },
            {
                name: 'showtype',
                params: {
                    option: 'on'
                },
                type: 3,
                defaults: ['on', 'off']
            }
        ];//-------array of datastore

        function params_generator() {
            $('#try').html(null);
            $.each(data, function (key_property, property) {
                $('#try').append(`
                    <div>
                        <h3>${property.name}</h3>
                        ${fields_generator(property)}
                        
                    </div>
                `)

            });
            button();
            eventRemove();
        }//-------create full HTML block

        function fields_generator(property) {
            var strol = '';

            $.each(property.params, function (key_param, param) {
                switch (property.type) {
                    case 1:
                    case 2:
                        strol += `
                    <label>${key_param}</label>
                    <div class="col-md-12">
                    <div class="col-md-8">
                    <input
                      type="text"
                      class="form-control"
                      data-name="${property.name}"
                      data-property="${key_param}"
                      value="${param}"
                      name="${property.name}-${key_param}" />
                    </div>
                    <div class="col-md-4 ">
                      <button class="btn btn-primary btn-md remover" data-name="${property.name}" data-property="${key_param}" data-value="${param}">Remove</button>
                    </div>
                    </div>
                      <br>`;
                        break;
                    case 3:
                    case 4:
                        strol += `
                    <label>${key_param}</label>
                    <select data-name="${property.name}" 
                            data-property="${key_param}">
                        ${options_generator(property.defaults)}
                    </select>`;
                        break;
                }
            });

            return strol;
        }//-------generate all fields for one type

        function options_generator(options) {
            var options_string = '';

            $.each(options, function (key_option, option){
                options_string += `<option value="${option}">${option}</option>`;
            });

            return options_string;
        }//-------generate fields for option

        function button() {


            $('#generate_button,#open_window').on('click', function () {
                if (this.id == 'generate_button') {
                    var a = '1';
                }
                else if (this.id == 'open_window') {
                    var a ='2';
                }


                $('input[type=text],select').each(function () {
                    var _name = $(this).data('name');
                    var _propertys = $(this).data('property');
                    var _value = $(this).val();


                    $.each(data, function (key_property, property) {
                        if (property.name == _name) {
                            data[key_property].params[_propertys] = _value;
                        }

                    });

                });

                urlgen(a);

            });
        }//-------------create button and start script for changing array from old one to new one

        function urlgen(a) {
            var strurl = '';
            $.each(data, function (key_property, property) {
                $.each(property.params, function (key_param, param) {

                    switch (property.type) {
                        case 1:strurl += `${property.name}[${key_param}]=${param}`;
                            break;
                        case 2:strurl += `${property.name}=${param}`;
                            break;
                        case 3:strurl += `${property.name}=${param}`;
                            break;
                        case 4:strurl += `${property.name}=${param}&listdetail=off`;
                            break;

                    }
                    strurl += '&';

                });

                return strurl;

            });

            strurl = strurl.replace(/#/g, "%23");
            addhref(strurl,a);

        }//-------------generate values from fields to string variable

        function addhref(strurl,a){
            var a_href = $('#frame').attr('data-original');
            var res = a_href.split("#");
            var newhref=res[0]+"?"+strurl+"#"+res[1];

            check_button(newhref,a);

        };//-------add to url our values

        function check_button(newhref,a){
            if( a =='1'){

                var sourcecode=`<iframe id="frame" src="${newhref}" height="700px" width="100%"></iframe>`;

                $('#frame').attr('src', '').attr('src',newhref);
                $('#Copycode').attr('data-clipboard-text',sourcecode);
            }
            else {
                window.open(newhref)
            }
        };

        $('.adder').on('click',function(){

            var _name= $('#name').val();
            var _property = $('#property').val();
            var _param = $('#value').val();
            var check=true;

            $.each(data, function(key_property, property) {

                if(property.name == _name) {
                    property.params[_property] = _param;
                    check=false;
                }
            });

            if(check == true){
                data.unshift({name:_name,params:{[_property]:_param},type:1})
            }

            params_generator();

        });//-------Add fields functioon

        function eventRemove() {
            $('.remover').on('click',function(){
                console.log('pet');
                var _name= $(this).data('name');
                var _propertys = $(this).data('property');
                var _param = $(this).data('value');
                console.log(_name,_propertys,_param);

                $.each(data, function(key_property, property) {

                    if (property.name == _name) {
                        delete data[key_property].params[_propertys];
                    }

                });

                params_generator();

            });//-------remove fields functioon
        }

        function change_frame_src(){
            $("#frame").attr("src","#/widget/restaurant/"+$rootScope.restaurants[0].id).attr("data-original","#/widget/restaurant/"+$rootScope.restaurants[0].id);
        }

        function CopyEvent() {
            $('#Copycode').click(function () {
                alert("The copy was successful!)");
            });
        }

        function getRestaurants(){
            return ProfileService.getRestaurants($rootScope.currentUser.id).then(function(response){
                $rootScope.restaurants = response.data;
            });
        }



        function init() {
            getRestaurants().then(function() {
                change_frame_src($rootScope.restaurants[0].id);
            });
            params_generator();
            CopyEvent();
        }//---------------initialise functions

        init();

    }

})();