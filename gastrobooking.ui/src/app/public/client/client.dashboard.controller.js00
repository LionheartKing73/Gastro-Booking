/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.client')
        .config(function(tagsInputConfigProvider) {
            tagsInputConfigProvider.setActiveInterpolation('tagsInput', { placeholder: true });
        })
    .controller('ClientDashboardController', ClientDashboardController);
    /*@ngNoInject*/
    function ClientDashboardController($rootScope,$scope,$timeout,ClientService, $state, $translate, $interval, $filter, $geolocation) {
        var vm = this;
        $rootScope.currentState = "dashboard";
        vm.currentTab = $rootScope.currentTab;
        vm.friendCircle = [];
        vm.quizSetting = [];
        vm.quizSettings = [];
        vm.quiz = [];
        vm.quizClientAll = [];
        vm.quizClient = [];
        vm.quizResult = [];
        vm.sum_price = [];
        vm.quiz_prize0 = [];
        vm.quiz_prize = [];
        vm.currency = "";
        vm.isNextQuiz = false;
        vm.percentage_discount = 0;
        vm.selectedAnswer = "";
        vm.strDayQuestion = "";
        vm.strBonusQuestion = "";
        vm.realQuestion = [];
        vm.q_testStart = false;
        vm.quiztime = 0;
        vm.currentTime = "";
        vm.remainHours = 0;
        vm.nextTime = 0;
        vm.ordernumbers = 0;
        vm.otherCircles = [];
        vm.friendRequests = [];
        vm.loadingFriendRequests = false;
        vm.sentFriendRequests = [];
        vm.friends = [];
        vm.connectSuccess = "";
        vm.respondToFriendRequestStatus = "";
        vm.doFade = false;
        vm.orders = [];
        vm.currentLanguage = "";
        vm.ordersDetail = [];
        vm.loading = false;
        vm.loadingBookingHistory = false;
        vm.loadingSaveChanges = false;
        vm.isOrderEmpty = false;
        vm.isOrderDetailEmpty =false;
        vm.currentPage = 1;
        vm.itemsPerPage = 5;
        vm.totalItems = 0;
        vm.statusNames = ['CLIENT.ORDERED', 'CLIENT.SENT', 'CLIENT.CONFIRMED', 'CLIENT.CANCELED', 'CLIENT.FINALIZED', 'CLIENT.NEW'];
        vm.sortStatus = {
            "status": {
                "new": 0,
                "sent": 0,
                "confirmed": 0,
                "canceled": 1,
                "finalized": 0,
                "all": 0,
            }
        };
        vm.isDayOrBonusTest = false;
        vm.isCanDayquestion = false;
        vm.isCanBonuseQuestion = false;
        vm.isBonusQuiz = false;

        vm.placeholder = 'CLIENT.ENTER FRIEND NAME';

        vm.language = {
            "ENG" : { "language" : "English", "short" : "ENG"},
            "CZE" : { "language" : "Česky", "short" : "CZE" }
        };
        vm.myDetailsSuccess = "";
        vm.myDetailsError = "";

        vm.getFriends = getFriends;
        vm.getFriendRequests = getFriendRequests;
        vm.getFriendCircle = getFriendCircle;
        vm.getSetting = getSetting;
        vm.startQuiz = startQuiz;
        vm.getQuizClient = getQuizClient;
        vm.changeDlg = changeDlg;
        vm.sendEmail = sendEmail;
        vm.nextQuestion = nextQuestion;

        vm.getOtherCircles = getOtherCircles;
        vm.respondToFriendRequest = respondToFriendRequest;
        vm.addFriends = addFriends;
        vm.getOrders = getOrders;
        vm.changeOrderDetailStatus = changeOrderDetailStatus;
        vm.changeOrderStatus = changeOrderStatus;
        vm.saveChanges = saveChanges;
        vm.respond = respond;
        vm.loading = false;
        vm.gotoRestaurantDetail = gotoRestaurantDetail;
        vm.printOrder = printOrder;
        vm.dbTest = dbTest;
        vm.getSumPrize = getSumPrize;
        vm.saveClient = saveClient;
        vm.closeAlert = closeAlert;
        vm.resize = resize;

        vm.bReceiveAllData = [];

        getQuizClient();
        getQuizPrize();
        getSumPrice();
        getSetting();

        getOrders();
        getFriendCircle();
        getOtherCircles();
        getFriendRequests();
        getSentFriendRequests();
        getCurrentClient();
        vm.updateNoDiet = updateNoDiet;

        isDayandBonusQuestion();

        $rootScope.$on('$translateChangeSuccess', function (a) {
            var currentLanguage = localStorage.getItem('current_language');
            vm.quizSetting = vm.quizSettings[currentLanguage];
            if (vm.quizSetting) {
                vm.quiztime = vm.quizSetting.quiz_delay_hrs;
                vm.currency = vm.quizSetting.currency_short;

                vm.sum_price.sum_price = vm.sum_price[currentLanguage].sum_price;
                vm.sum_price.sum_price_for_bonus = vm.sum_price[currentLanguage].sum_price_for_bonus;
                vm.sum_price.total_bonus_quiz_count = Math.floor(vm.sum_price[currentLanguage].sum_price_for_bonus / vm.quizSetting.quiz_bonus_order);
                vm.sum_price.bonus_quiz_count = vm.sum_price.total_bonus_quiz_count - vm.sum_price[currentLanguage].n_bonus_quiz_count;

                vm.quizClient = vm.quizClientAll[currentLanguage];

                vm.quiz_prize = vm.quiz_prize0[currentLanguage];

                vm.bReceiveAllData.quizQuiz = true;

                isDayandBonusQuestion();
            }
        });

        function getFriends(query){
            debugger;
            return ClientService.getFriends(query).then(function(response){
                debugger;
                return response.data
            });
        }

        function getFriendCircle(){
            debugger;
            ClientService.getFriendCircle().then(function(response){
                debugger;
                vm.friendCircle = response.data;
            },function(error){

            });
        }

        function getQuizClient() {
            ClientService.getQuizClient().then(function(response){
                //debugger;
                vm.quizClientAll = response;

                var currentLanguage = localStorage.getItem('current_language');
                vm.quizClient = vm.quizClientAll[currentLanguage];

                console.log(vm.quizClient);

                vm.bReceiveAllData.quizClient = true;
            },function(error){

            });
        }

        function isDayandBonusQuestion() {
            /*
            *   Delay, Percent, SumPrice
            *   vm.quizClient
            *   vm.quizSetting
            *   vm.order
            **/

            var isDelay = false;
            var isNextStep = false;
            var percentage = 0;
            var sumprice = 0;

            // real delay hrs
            var date1 = new Date(vm.quizClient.lastanswered);   // Last Answered Time
            var date2 = new Date();                         // Current Time
            var timezoneDelay = - date2.getTimezoneOffset() / 60;
            date1.setHours(date1.getHours() + timezoneDelay);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var delayQuiz = Math.ceil(timeDiff/ (1000 * 3600));
            if(!delayQuiz){
                delayQuiz = vm.quizSetting.quiz_delay_hrs + 1;
            }
            // setting.quiz_delay_hrs
            vm.quiztime = vm.quizSetting.quiz_delay_hrs;

            isDelay = (delayQuiz > vm.quizSetting.quiz_delay_hrs) ? true : false;

            percentage = vm.quizClient.daily_percentage_discount;
            sumprice = vm.sum_price.sum_price;
            if(percentage == 10 * (vm.quizClient.percentage_step + 1)){
                if(sumprice >= vm.quizSetting.quiz_min_order){
                    isNextStep = true;
                    updateLastCrossingTime();
                }
                else{
                    isNextStep = false;
                }

            }
            else if(percentage < 10 * (vm.quizClient.percentage_step + 1) ){
                isNextStep = true;
            }

            // Daily Quiz
            vm.isCanDayquestion = (isDelay && isNextStep) ? true : false;
            if(vm.isCanDayquestion == true) {       // isDelay = true, isNextStep = true.
                vm.strDayQuestion = "CLIENT.AVAILABLE";
                vm.strDayQuestion2 = "";
                vm.nextTime = "";
                vm.remainHours = "";
            } else if(isNextStep == false){         // isDelay = true || false, isNextStep = false.
                vm.strDayQuestion = "CLIENT.UNAVAILABLE";
                vm.strDayQuestion2 = "";
                vm.nextTime = "";
                vm.remainHours = "";
            } else {                                // isDelay = false, isNextStep = true.
                vm.remainHours = vm.quizSetting.quiz_delay_hrs - delayQuiz;
                vm.nextTime = new Date(date1);
                vm.nextTime.setHours(date1.getHours() + parseInt(vm.quizSetting.quiz_delay_hrs));
                vm.nextTime = $filter('date')(vm.nextTime, 'medium');
                vm.strDayQuestion = "CLIENT.AVAILABLE IN "; // + vm.remainHours + " CLIENT.HOURS, " + vm.nextTime;
                vm.strDayQuestion2 = "CLIENT.HOURS";
            }

            // Bonus Quiz
            vm.isCanBonuseQuestion = (vm.sum_price.bonus_quiz_count > 0) ? true : false;
            if(vm.isCanDayquestion == true) vm.isDayOrBonusTest = true;
            else if(vm.isCanBonuseQuestion == true) vm.isDayOrBonusTest = false;
            console.log(vm.isCanBonuseQuestion);
            vm.strBonusQuestion = "CLIENT.AVAILABLE";

            if(vm.isCanDayquestion && vm.isCanBonuseQuestion){
                vm.isNextQuiz = true;
            }
            else if(vm.isCanDayquestion && !vm.isCanBonuseQuestion){
                vm.isNextQuiz = false;
            }
            else if(!vm.isCanDayquestion && vm.isCanBonuseQuestion){
                vm.isNextQuiz = (vm.sum_price.bonus_quiz_count > 1) ? true : false;
            }
            else if(!vm.isCanDayquestion && !vm.isCanBonuseQuestion){
                vm.isNextQuiz = false;
            }

            return true;
        }

        function getSetting(){
            ClientService.getSetting().then(function(response){
                //debugger;
                if(response.error) return;
                vm.quizSettings = response;

                var currentLanguage = localStorage.getItem('current_language');
                vm.quizSetting = vm.quizSettings[currentLanguage];
                vm.quiztime = vm.quizSetting.quiz_delay_hrs;
                vm.currency = vm.quizSetting.currency_short;

                if (vm.sum_price.cs) {
                    vm.sum_price.sum_price = vm.sum_price[currentLanguage].sum_price;
                    vm.sum_price.sum_price_for_bonus = vm.sum_price[currentLanguage].sum_price_for_bonus;
                    vm.sum_price.total_bonus_quiz_count = Math.floor(vm.sum_price[currentLanguage].sum_price_for_bonus / vm.quizSetting.quiz_bonus_order);
                    vm.sum_price.bonus_quiz_count = vm.sum_price.total_bonus_quiz_count - vm.sum_price[currentLanguage].n_bonus_quiz_count;
                }

                vm.bReceiveAllData.quizQuiz = true;

                isDayandBonusQuestion();
            },function(error){

            });
        }

        function startQuiz() {
            if(!(vm.isCanDayquestion || vm.isCanBonuseQuestion))
                return;

            $("#start_quiz_btn").attr("data-target", "#restt_location");
            ClientService.startQuiz().then(function(response){
                //debugger;
                vm.quiz = response.data;
                if(!vm.quiz.q_photo){
                    vm.quiz.q_photo = "logo.png"
                }

                vm.quizResult.percentage = vm.quiz.percentage;
                vm.q_testStart = true;
                // Init Variable
                vm.quiztime = vm.quizSetting.quiz_delay_hrs;
                vm.quizResult.isRight = false;
                vm.quizResult.rate_quality = 0;
                vm.quizResult.rate_difficulty = 0;
                vm.quizResult.answer = 'x';
                vm.currentTime = $filter('date')(new Date(), 'medium');

                // Init Quiz
                vm.realQuestion = {
                    "question": vm.quiz.ENG_question,
                    "answer_a": vm.quiz.ENG_a,
                    "answer_b": vm.quiz.ENG_b,
                    "answer_c": vm.quiz.ENG_c,
                    "answer_d": vm.quiz.ENG_d,
                    "q_note": vm.quiz.q_note,
                    "q_rightans": "",
                    "result_a": 3,
                    "result_b": 3,
                    "result_c": 3,
                    "result_d": 3
                }

                vm.quizResult.ID_quiz = vm.quiz.ID;

                if(localStorage.getItem('current_language') != "en" && vm.quiz.CZE_question != null) {
                    vm.realQuestion.question = vm.quiz.CZE_question;
                    vm.realQuestion.answer_a = vm.quiz.CZE_a;
                    vm.realQuestion.answer_b = vm.quiz.CZE_b;
                    vm.realQuestion.answer_c = vm.quiz.CZE_c;
                    vm.realQuestion.answer_d = vm.quiz.CZE_d;
                }
                switch(vm.quiz.q_right){
                    case 'A':
                        vm.realQuestion.result_a = 2;
                        vm.realQuestion.q_rightans = vm.realQuestion.answer_a;
                        break;
                    case 'B':
                        vm.realQuestion.result_b = 2;
                        vm.realQuestion.q_rightans = vm.realQuestion.answer_b;
                        break;
                    case 'C':
                        vm.realQuestion.result_c = 2;
                        vm.realQuestion.q_rightans = vm.realQuestion.answer_c;
                        break;
                    case 'D':
                        vm.realQuestion.result_d = 2;
                        vm.realQuestion.q_rightans = vm.realQuestion.answer_d;
                        break;
                    default:
                        break;
                }
                console.log(vm.realQuestion);
            }, function(error){
                console.log('Did not get question data');
            });
        }

        $interval(function(){
            if(vm.q_testStart){
                vm.quiztime -= 1;
                if(vm.quiztime <= 0 && vm.q_testStart == true){
                    $("#restt_location").modal('hide');
                    vm.quiztime = vm.quizSetting.quiz_delay_hrs;

                    sendQuizResult();
                    vm.q_testStart = false;
                }


                var percent = 100 * vm.quiztime / vm.quizSetting.quiz_delay_hrs;
                // console.log(percent + " " + vm.q_testStart);
                $(".progress-bar").css("width", percent + "%");
                if(percent >= 75){
                    $(".progress-bar").removeClass("progress-bar-danger");
                    $(".progress-bar").removeClass("progress-bar-warning");
                    $(".progress-bar").removeClass("progress-bar-info");
                    $(".progress-bar").addClass("progress-bar-success");
                }
                if(percent < 75 && percent >= 45) {
                    $(".progress-bar").removeClass("progress-bar-success");
                    $(".progress-bar").addClass("progress-bar-info");
                } else if(percent < 45 && percent >= 20){
                    $(".progress-bar").removeClass("progress-bar-info");
                    $(".progress-bar").addClass("progress-bar-warning");
                } else if(percent < 20){
                    $(".progress-bar").removeClass("progress-bar-warning");
                    $(".progress-bar").addClass("progress-bar-danger");
                }
            }

            if(vm.bReceiveAllData.sumPrice == true && vm.bReceiveAllData.quizPrize == true
                && vm.bReceiveAllData.quizClient == true && vm.bReceiveAllData.quizQuiz == true){
                vm.bReceiveAllData.sumPrice = false;
                vm.bReceiveAllData.quizPrize = false;
                vm.bReceiveAllData.quizClient = false;
                vm.bReceiveAllData.quizQuiz = false;
                isDayandBonusQuestion();


            }
        }, 1000);

        $interval(function(){
            if(vm.currentLanguage != localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language){
                vm.currentLanguage = localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language;
            }
        }, 100);

        function changeDlg(){
	    //debugger;
            $("#restt_location").modal('hide');
            vm.quiztime = vm.quizSetting.quiz_delay_hrs;
            sendQuizResult();
            vm.q_testStart = false;

            var percent = 100 * vm.quiztime / vm.quizSetting.quiz_delay_hrs;
            console.log(percent + " " + vm.q_testStart);
            $(".progress-bar").css("width", percent + "%");
            if(percent >= 75){
                $(".progress-bar").removeClass("progress-bar-danger");
                $(".progress-bar").removeClass("progress-bar-warning");
                $(".progress-bar").removeClass("progress-bar-info");
                $(".progress-bar").addClass("progress-bar-success");
            }
            if(percent < 75 && percent >= 45) {
                $(".progress-bar").removeClass("progress-bar-success");
                $(".progress-bar").addClass("progress-bar-info");
            } else if(percent < 45 && percent >= 20){
                $(".progress-bar").removeClass("progress-bar-info");
                $(".progress-bar").addClass("progress-bar-warning");
            } else if(percent < 20){
                $(".progress-bar").removeClass("progress-bar-warning");
                $(".progress-bar").addClass("progress-bar-danger");
            }

            $(".quiz-content2 > div").removeClass("in active");
            $("#quiz-step1").addClass("in active");
            $("input[name='credit-card']").prop("checked",false);
            $("input[name='credit-card1']").prop("checked",false);
            $("input[name='credit-card']").removeClass("glyphicon-star");
            $("input[name='credit-card1']").removeClass("glyphicon-star");

            $("span[name='quality']").removeClass('glyphicon-star').addClass('glyphicon-star-empty');
            $("#start_quiz_btn").attr("data-target", "");
            vm.q_testStart = false;
        }

        function getSumPrize() {
            var total_discount = 0;
            var percentage_discount = 0;
            var total_percentage_discount = 0;
            for(var i = 0, length1 = vm.quiz_prize.length; i < length1; i++){
                total_discount += parseInt(vm.quiz_prize[i].prize);
                percentage_discount -= parseInt(vm.quiz_prize[i].percentage);
                total_percentage_discount += parseInt(vm.quiz_prize[i].percentage);
            }
            percentage_discount += vm.quizClient.percentage_discount;
            return {
                "total_discount": total_discount,
                "percentage_discount": percentage_discount,
                "total_percentage_discount": total_percentage_discount
            };
        }
        function dbTest() {
            //getSetting();

            console.log("Update");
        }

        function nextQuestion(){
            if(vm.isNextQuiz){
                sendQuizResult();
                ClientService.startQuiz().then(function(response){
                    //debugger;
                    vm.quiz = response.data;
                    if(!vm.quiz.q_photo){
                        vm.quiz.q_photo = "logo.png"
                    }
                    vm.quizResult.percentage = vm.quiz.percentage;
                    // Init Variable
                    vm.quiztime = vm.quizSetting.quiz_delay_hrs;
                    vm.quizResult.isRight = false;
                    vm.quizResult.rate_quality = 0;
                    vm.quizResult.rate_difficulty = 0;
                    vm.quizResult.answer = 'x';
                    vm.currentTime = $filter('date')(new Date(), 'medium');
                    // Init Quiz
                    vm.realQuestion = {
                        "question": vm.quiz.ENG_question,
                        "answer_a": vm.quiz.ENG_a,
                        "answer_b": vm.quiz.ENG_b,
                        "answer_c": vm.quiz.ENG_c,
                        "answer_d": vm.quiz.ENG_d,
                        "q_note": vm.quiz.q_note,
                        "q_rightans": "",
                        "result_a": 3,
                        "result_b": 3,
                        "result_c": 3,
                        "result_d": 3
                    }

                    vm.quizResult.ID_quiz = vm.quiz.ID;

                    if(localStorage.getItem('currentlanguage') != "en" && vm.quiz.CZE_question != null) {
                        vm.realQuestion.question = vm.quiz.CZE_question;
                        vm.realQuestion.answer_a = vm.quiz.CZE_a;
                        vm.realQuestion.answer_b = vm.quiz.CZE_b;
                        vm.realQuestion.answer_c = vm.quiz.CZE_c;
                        vm.realQuestion.answer_d = vm.quiz.CZE_d;
                    }
                    switch(vm.quiz.q_right){
                        case 'A':
                            vm.realQuestion.result_a = 2;
                            vm.realQuestion.q_rightans = vm.realQuestion.answer_a;
                            break;
                        case 'B':
                            vm.realQuestion.result_b = 2;
                            vm.realQuestion.q_rightans = vm.realQuestion.answer_b;
                            break;
                        case 'C':
                            vm.realQuestion.result_c = 2;
                            vm.realQuestion.q_rightans = vm.realQuestion.answer_c;
                            break;
                        case 'D':
                            vm.realQuestion.result_d = 2;
                            vm.realQuestion.q_rightans = vm.realQuestion.answer_d;
                            break;
                        default:
                            break;
                    }

                    $("#next-question-r").attr("ng-href", "#quiz-step1");
                    $("#next-question-w").attr("ng-href", "#quiz-step1");
                    var percent = 100 * vm.quiztime / vm.quizSetting.quiz_delay_hrs;
                    $(".progress-bar").css("width", percent + "%");
                    if(percent >= 75){
                        $(".progress-bar").removeClass("progress-bar-danger");
                        $(".progress-bar").removeClass("progress-bar-warning");
                        $(".progress-bar").removeClass("progress-bar-info");
                        $(".progress-bar").addClass("progress-bar-success");
                    }
                    if(percent < 75 && percent >= 45) {
                        $(".progress-bar").removeClass("progress-bar-success");
                        $(".progress-bar").addClass("progress-bar-info");
                    } else if(percent < 45 && percent >= 20){
                        $(".progress-bar").removeClass("progress-bar-info");
                        $(".progress-bar").addClass("progress-bar-warning");
                    } else if(percent < 20){
                        $(".progress-bar").removeClass("progress-bar-warning");
                        $(".progress-bar").addClass("progress-bar-danger");
                    }            

                    $(".quiz-content2 > div").removeClass("in active");
                    $("#quiz-step1").addClass("in active");
                    $("input[name='credit-card']").prop("checked",false);
                    $("input[name='credit-card1']").prop("checked",false);
                    $("input[name='credit-card']").removeClass("glyphicon-star");
                    $("input[name='credit-card1']").removeClass("glyphicon-star");            
                    
                    $("span[name='quality']").removeClass('glyphicon-star').addClass('glyphicon-star-empty');
                    $("#start_quiz_btn").attr("data-target", "");
                    vm.q_testStart = true;
                    vm.isNextQuiz = false;
                }, function(error){
                    console.log('Did not get question data');
                });
            }
            else{
                $("#next-question-r").attr("ng-href", "");
                $("#next-question-w").attr("ng-href", "");
                alert("Sorry, you can not quiz.");
            }
        }
        function sendEmail(selected){
            var text;
            if(selected == "right"){
                text = $('#email-right').val();
            }
            else if(selected == "wrong"){
                text = $('#email-wrong').val();
            }
            else{
                text = "None Content.";
            }

            var user = JSON.parse(localStorage.getItem('user'));
            var content = "id: " + vm.quiz.ID + "<br>" +
                          "question: " + vm.quiz.CZE_question + "<br>" +
                          "answer_a: " + vm.quiz.CZE_a + "<br>" +
                          "answer_b: " + vm.quiz.CZE_b + "<br>" +
                          "answer_c: " + vm.quiz.CZE_c + "<br>" +
                          "answer_d: " + vm.quiz.CZE_d + "<br>" +
                          "note: " + vm.quiz.q_note + "<br>" +
                          "client name: " + user.name + "<br>" +
                          "client email: " + user.email + "<br><br>" +
                          text;

            var result = {
                "content": content,
                "from": user.email
            }

            ClientService.sendEmail(result).then(function(response){
                alert(response);
            }, function(error){

            });
        }

        function sendQuizResult() {     // Send Quiz Result
	    //debugger;
            var deferred = $.Deferred();

            vm.quizResult.isRight = (vm.selectedAnswer == vm.realQuestion.q_rightans) ? true : false;
            console.log($('#count1').attr("value"));
            console.log($('#count2').attr("value"));
            if($('#count1').attr("value") != 0) {
                vm.quizResult.rate_quality = $('#count1').attr("value");
            }
            else if($('#count2').attr("value") != 0){
                vm.quizResult.rate_quality = $('#count2').attr("value");
            }
            console.log(vm.quizResult.rate_quality);
            $('#count1').attr("value", 0);
            $('#count2').attr("value", 0);

            var isBonusQuiz = vm.isDayOrBonusTest ? 0 : 1;
            var quizClient = {
                "ID_quiz": vm.quizResult.ID_quiz,
                "bonus": isBonusQuiz,
                "answer": vm.quizResult.answer,
                "answered": new Date(),
                "rate_difficulty": vm.quizResult.rate_difficulty,
                "rate_quality": vm.quizResult.rate_quality,
                "isRight": vm.quizResult.isRight,
                "percentage": vm.quizResult.percentage,
                "lang": localStorage.getItem('current_language') == 'cs' ? 'CZE' : 'ENG'
            };

            ClientService.addQuizClient(quizClient).then(function(response){
                getQuizClient();
                getQuizPrize();
                getSumPrice();
                getSetting();
                deferred.resolve(response);
            },function(error) {
                deferred.reject(error);
            });

            return deferred.promise();
        }
        function getQuizPrize() {   // Get Percentage
            ClientService.getQuizPrize().then(function(response){
                vm.quiz_prize0 = response;

                var currentLanguage = localStorage.getItem('current_language');
                vm.quiz_prize = vm.quiz_prize0[currentLanguage];

                vm.bReceiveAllData.quizPrize = true;
            }, function(error){

            });
        }
        function getSumPrice() {    // Sum Price, Calculate Bonus Condition
            ClientService.getSumPrice().then(function(response){
                vm.sum_price = response;
                if (vm.quizSetting.quiz_bonus_order) {
                    var currentLanguage = localStorage.getItem('current_language');
                    vm.sum_price.sum_price = vm.sum_price[currentLanguage].sum_price;
                    vm.sum_price.sum_price_for_bonus = vm.sum_price[currentLanguage].sum_price_for_bonus;
                    vm.sum_price.total_bonus_quiz_count = Math.floor(vm.sum_price[currentLanguage].sum_price_for_bonus / vm.quizSetting.quiz_bonus_order);
                    vm.sum_price.bonus_quiz_count = vm.sum_price.total_bonus_quiz_count - vm.sum_price[currentLanguage].n_bonus_quiz_count;
                }
                vm.bReceiveAllData.sumPrice = true;
            }, function(error){

            });
        }

        function updateLastCrossingTime() {
            ClientService.updateLastCrossingTime().then(function(response){

            }, function(error){

            });
        }

        function getOtherCircles(){
            debugger;
            ClientService.getOtherCircles().then(function(response){
                debugger;
                vm.otherCircles = response.data;
            },function(error){

            });
        }

        function respondToFriendRequest(response,friendId){
            debugger;
            var response = {
                "response":{
                    "response":response,
                    "ID_grouped_client": friendId    
                }

            };
            ClientService.respondToFriendRequest(response).then(function(respons){
                //debugger;
                var approved = response.response.response;
                vm.respondToFriendRequestStatus = approved == 'Y' ? "CLIENT.Accepted friend request!" : (approved == 'D') ? "CLIENT.Friend request declined!" : (approved == 'R') ? "CLIENT.User blocked successfully!" : "";
                vm.friends = [];
                $timeout(function(){
                    vm.doFade = true;
                    vm.respondToFriendRequestStatus = "";
                }, 2500);
                getFriendRequests();
                getFriendCircle();
                getOtherCircles();
            },function(error){
                debugger;
            });
        }
        function respond(response, friendId){
            var response = {
                "response":{
                    "response":response,
                    "ID_grouped_client": friendId
                }

            };
            ClientService.respondToFriendRequest(response).then(function(response){
                debugger;
                getFriendCircle();
                getOtherCircles();
            }, function(error){
                debugger;
            });
        }

        function getFriendRequests(){
            vm.loadingFriendRequests = true;
            debugger;
            ClientService.getFriendRequests().then(function(response){
                debugger;
                vm.friendRequests = response.data;
                vm.loadingFriendRequests = false;
            },function(error){
                vm.loadingFriendRequests = false;
                debugger;
            });
        }
        function addFriends(isValid){
            debugger;
            if (isValid){
                var friends = {"friends":vm.friends};

                ClientService.addFriends(friends).then(function(response){
                    debugger;
                    vm.connectSuccess = "CLIENT.Request sent successfully!";
                    vm.friends = [];
                    $timeout(function(){
                        vm.doFade = true;
                        vm.connectSuccess = "";
                    }, 2500);
                    getSentFriendRequests();
                },function(error) {
                    debugger;
                });
            }

        }
        function getSentFriendRequests(){
            debugger;
            ClientService.getSentFriendRequests().then(function(response){
                debugger;
                vm.sentFriendRequests = response.data;
            },function(error){
                debugger;
            });
        }

        function getOrders() {
            debugger;
            vm.loading = true;
            ClientService.getOrders(vm.currentPage).then(function (response) {
                debugger;
                vm.orders = response.data;
                vm.totalItems = response.meta.pagination.total;
                vm.itemsPerPage = response.meta.pagination.per_page;

                if (vm.orders.length == 0) {
                    vm.isOrderEmpty = true;
                }
                vm.ordernumbers = 0;
                for(var i = 0; i < vm.orders.length ; i++) {
                    vm.ordernumbers += vm.orders[i].order_number;
                }
                // alert(vm.ordernumbers);
                vm.loading = false;
                // arrangeOrdersDetail();
                debugger;
            }, function (error) {
                debugger;
                vm.loading = false;
            })
        }

        function printOrder(order) {
            ClientService.printOrder(order.ID_orders, $translate.use()).then(function (response) {
                var printWindow = window.open('', 'PRINT', 'height=600,width=800');
                printWindow.document.write(response);
                printWindow.document.close(); // necessary for IE >= 10
                printWindow.focus(); // necessary for IE >= 10*/

                $timeout(function(){ printWindow.print() }, 100);
                // printWindow.close();
            }, function (error) {
                alert('Print error!');
            });
        }

        function changeOrderDetailStatus(newStatus, order, index) {
            debugger;
            if (newStatus == 6)
                $rootScope.$broadcast('orders-detail-changed');
            order.orders_detail.data[index].status = newStatus;
            debugger;
        }

        function changeOrderStatus(newStatus, order) {
            debugger;
            vm.loading = true;
            order.status = newStatus;
            var n_order = {
                "order": order,
                'lang': localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language

            };
            ClientService.updateOrder(n_order).then(function (response) {
                debugger;
                $rootScope.$broadcast('orders-detail-changed');
                vm.getOrders();
                vm.loading = false;
            }, function (error) {
                debugger;
                vm.loading = false;
            })
        }
        function saveChanges(order, index) {
            debugger;
            vm.loadingSaveChanges = true;
            var n_order_detail = {
                "orders_detail": order.orders_detail.data,
                "save" : true,
                "lang": localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language
            };
            ClientService.updateOrderDetails(n_order_detail, true).then(function (response) {
                debugger;
                vm.loadingSaveChanges = false;
                $('#orderDetail' + index).modal('hide');
                $rootScope.$broadcast('orders-detail-changed');
                getOrders();
            }, function (error) {
                debugger;
                vm.loadingSaveChanges = false;
                $('#orderDetail' + index).modal('hide');
            })
        }

        function gotoRestaurantDetail(order, index) {
            debugger;
            var id = '#orderDetail'+ index;
            $(id).modal('hide');
            debugger;
            $state.go('main.restaurant_detail', {'restaurantId': order.ID_restaurant});
        }


        //Pop over functions
        var trigger = $('button');

        function showTip() {
            if (! $('#tip').is(':visible')) {
                trigger.click();
            }
        }

        function hideTip() {
            if ($('#tip').is(':visible')) {
                trigger.click();
            }
        }

        // trigger.mouseenter(showTip);

        $(document).on('mouseleave', '#tip', hideTip);
        //End pop over functions

        function resize(){
            $timeout(function(){
                $('#map_holder').locationpicker('autosize');
            }, 200);
        }

        function getLocation() {
            if ( vm.myDetails.latitude === null || vm.myDetails.longitude === null) {
                $geolocation.getCurrentPosition({
                    timeout: 600
                }).then(function(position) {
                    $timeout(function() {
                        vm.myDetails.latitude = position.coords.latitude;
                        vm.myDetails.longitude = position.coords.longitude;
                        locale();
                    }, 10);
                }, function(error){
                    $timeout(function() {
                        vm.location_error = "We couldn't locate your location! Please enter your location in the following box";
                        vm.myDetails.latitude = 49.8209226;
                        vm.myDetails.longitude = 18.262524299999995;
                        locale();
                    }, 10);
                });
            } else {
                locale();
            }
        }

        function locale(){
            var locationpicker = $('#map_holder');
            //locationpicker.locationpicker('autosize');
            locationpicker.locationpicker({
                location: {latitude: vm.myDetails.latitude, longitude: vm.myDetails.longitude },
                radius: 300,
                zoom: 15,
                inputBinding: {
                    locationNameInput: $('#address_input')
                },
                onchanged: function(currentLocation) { 
                    vm.myDetails.latitude = currentLocation.latitude;
                    vm.myDetails.longitude = currentLocation.longitude;
                },
                enableAutocomplete: true
            });
        }

        function getDiets() {
            ClientService.getDiets().then(function(response){
                vm.diets = {};

                angular.forEach(vm.language, function (value, key) {
                    vm.diets[key] = { 'null': { 'id' : '-1', 'name' : "No diet", 'cust_order' : 0, 'lang' : "none"} };
                });

                angular.forEach(response.data, function (value, key) {
                    vm.diets[value.lang]["+" + value.id] = value;
                });
            }, function(error){
                debugger;
            });
        }

        function updateNoDiet() {
            vm.myDetails.ID_diet = '-1';
        }

        function getCurrentClient() {
            ClientService.getCurrentClient().then(function(response){
               vm.myDetails = response.data;
               getDiets();
               vm.myDetails.ID_diet = (vm.myDetails.ID_diet == null) ? "-1" : vm.myDetails.ID_diet;
               if (!vm.myDetails.lang) {
                   var code = ClientService.getLanguageCode();
                   vm.myDetails.lang = code;
               }
               getLocation();
            }, function(error){
                debugger;
            });
        }

        function saveClient(isValid){
            if (isValid)
            {
                vm.myDetails.ID_diet = (vm.myDetails.ID_diet == '-1') ? null : vm.myDetails.ID_diet;
                ClientService.updateClient(vm.myDetails).then(function(response){
                    vm.myDetailsSuccess = "Changes have been successfully saved!";
                    vm.myDetails.password = "";
                    vm.myDetails.confirm_password = "";
                    window.scrollTo(0, 0);
                });
            }
        }

        function closeAlert() {
            vm.myDetailsSuccess = "";
            vm.location_error = "";
        }
    }

})();
