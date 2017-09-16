/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';
    'use Math'

    angular
        .module('app.client')
        .config(function(tagsInputConfigProvider) {
            tagsInputConfigProvider.setActiveInterpolation('tagsInput', { placeholder: true });
        })
    .controller('ClientDashboardController', ClientDashboardController);
    /*@ngNoInject*/
    function ClientDashboardController($rootScope,$scope,$timeout,ClientService, $state, $translate, $interval, $filter, $geolocation) {
        var client_percentage = 0;
        var commision_split = 0;
        var vm = this;
        $rootScope.currentState = "dashboard";
        vm.commission = 0;
        vm.client_commission = 0;
        vm.member_commission = 0;
        vm.total_book = 0;
        vm.client_book = 0;
        vm.member_book = 0;
        vm.total_spending = 0;
        vm.client_spending = 0;
        vm.member_spending = 0;
        vm.total = 0;
        vm.client = 0;
        vm.member = 0;
        vm.getPaied_price = 0;
        vm.min_remuneration = 0;
        vm.friend_commission = 0;
        vm.friend_spending = 0;
        vm.friend_book = 0;
        vm.order_payment = [];
        vm.billing = [];
        vm.firstMembers = [];
        vm.pay_date = new Date();
        vm.status = true;
        vm.IamMember="CLIENT.I AM A MEMBER";
        vm.AccountNumber = "CLIENT.ACCOUNT NUMBER";
        vm.BankCode = "CLIENT.BANK CODE";
        vm.Remunerations = "CLIENT.REMUNERATIONS";
        vm.Bookings = "CLIENT.BOOKINGS";
        vm.Spendings = "CLIENT.SPENDINGS";
        vm.Billing = "CLIENT.BILLING";
        vm.Total = "CLIENT.TOTAL";
        vm.Your = "CLIENT.YOUR";
        vm.Getpaid = "CLIENT.GETPAID";
        vm.Unpaid = "CLIENT.UNPAID";
        vm.Members = "CLIENT.MEMBER_SMALL";
        vm.ConnectionString = "CLIENT.CONNECTIONsting";
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
        vm.currency = 'Kč';
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
        vm.getbBookings = getbBookings;
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
        vm.getPaied = getPaied;
        vm.bReceiveAllData = [];
        vm.get_paid_date = get_paid_date;
        vm.last_pay_date = "";

        get_paid_date();
        getQuizClient();
        getQuizPrize();
        getSumPrice();
        getSetting();

        // getOrders();
        getbBookings();
        // getFriendCircle();
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

        function get_paid_date() {
            ClientService.getClient_payment().then(function (response) {
                    var order_payment = response.client_payments;
                    if(order_payment == undefined){
                        var len = 0;
                        vm.last_pay_date = new Date(1900, 1, 15, 12, 12);
                    }
                    else{
                        var len = order_payment.length;
                        var last_pay_date = order_payment[0].created_at;
                        for (var i = 0; i < len; i++) {
                            if (order_payment[i].created_at > last_pay_date) {
                                last_pay_date = order_payment[i].created_at;
                            }
                        }
                        var yr1   = parseInt(last_pay_date.substring(0,4));
                        var mon1  = parseInt(last_pay_date.substring(5,7));
                        var dt1   = parseInt(last_pay_date.substring(8,10));
                        var hour  = parseInt(last_pay_date.substring(11,13));
                        var minute  = parseInt(last_pay_date.substring(14,16));
                        var date1 = new Date(yr1, mon1-1, dt1, hour, minute);
                        vm.last_pay_date = date1;
                    }
                    console.log(vm.last_pay_date);
                }, function (error) {
                    console.log("error");
            });
        }

        function getPaied() {

            
            ClientService.getClient().then(function(response){
                var created_at = new Date();
                var data = {
                    "own_turnover": vm.client_spending,
                    "member_turnover": vm.member_spending,
                    "own_remuneration": vm.client_commission,
                    "member_remuneration": vm.member_commission,
                    "created_at": created_at,
                };
                vm.min_remuneration = response.data.setting[0].min_remuneration;
                var accountNumber = response.data.client[0].account_number;
                var bankCode = response.data.client[0].bank_code;
                var phone = response.data.client[0].phone;
                var lang = response.data.client[0].lang;
                
                if (vm.commission<vm.min_remuneration) {
                    var contentText = "Minimal amount is " + vm.min_remuneration + "Kč";
                    alert(contentText);
                }
                else{
                    if(accountNumber){
                        ClientService.getPaied(data).then(function(response){
                            debugger;
                        },function(error){
                            console.log(error);
                        });
                        var total = vm.commission;
                        getPaiedEmail(accountNumber, bankCode, phone, total, lang);
                        vm.total = (vm.total + vm.commission)*100;
                        vm.total = Math.floor(vm.total)/100;
                        vm.member = (vm.member + vm.member_commission)*100;
                        vm.member = Math.floor(vm.member)/100;
                        vm.client = (vm.client + vm.client_commission)*100;
                        vm.client = Math.floor(vm.client)/100;
                        vm.getPaied_price = vm.commission;
                        vm.commission = 0;
                        vm.client_commission = 0;
                        vm.member_commission = 0;
                        vm.total_spending = 0;
                        vm.client_spending = 0;
                        vm.member_spending = 0;
                        vm.status = false;
                        get_paid_date();
                    }
                    else{
                        alert("Please register your Account Number in the My Details section!");
                    }
                }
            },function(error){
                console.log(error);
            });
        }

        function getFriends(query){
            debugger;
            return ClientService.getFriends(query).then(function(response){
                debugger;
                return response.data
            });
        }

        function getFriendCircle(){
            vm.loading = true;
            ClientService.getFriendCircle().then(function(response){
                debugger;
                var total_spending = 0;
                var client_spending = 0;
                var member_spending = 0;
                var commission = 0;
                var client_commission = 0;
                var member_commission = 0;
                var total_book = 0;
                var client_book = 0;
                var member_book = 0;
                vm.friendCircle = response.data;
                var arr = [];
                for (var k = 0; k < vm.friendCircle.length; k++) {
                    vm.friendCircle[k]['commission'] = 0;
                    vm.friendCircle[k]['spending'] = 0;
                    vm.friendCircle[k]['book'] = 0;
                    vm.friendCircle[k]['flag'] = 0;
                    arr[k] = parseInt(vm.friendCircle[k].ID_grouped_client);
                    if(vm.friendCircle[k].precedings == "0"){
                        vm.friendCircle[k]['flag'] = 1; 
                    }
                    // for (var i = 0; i < vm.firstMembers.length; i++) {
                    //    console.log(vm.firstMembers); 
                        // if(vm.firstMembers[i] == arr[k]){
                        //    vm.friendCircle[k]['flag'] = 1; 
                        // }
                    // }
                }
                console.log(arr);
                ClientService.getAllOrdersArray(arr).then(function (response) {
                    var data = response.data;
                    for (var k = 0; k < data.length; k++) {
                        if (vm.friendCircle[k]['flag'] == 1){
                            var friend = data[k];
                            var order_detail = friend.orderDetail;
                            var len = order_detail.length;
                            var commision_split = parseFloat(friend.setting[0].commission_split);
                            var client_currency = friend.setting[0].currency_short;
                            for (var i = 0; i < len; i++) {
                                var order = order_detail[i].orders_detail;
                                for (var j = 0; j < order.length; j++) {
                                    var client_percentage = parseFloat(order[j].menu_list.client_percentage);
                                    var price = parseInt(order[j].price);
                                    var yr1   = parseInt(order[j].serve_at.substring(0,4));
                                    var mon1  = parseInt(order[j].serve_at.substring(5,7));
                                    var dt1   = parseInt(order[j].serve_at.substring(8,10));
                                    var hour  = parseInt(order[j].serve_at.substring(11,13));
                                    var minute  = parseInt(order[j].serve_at.substring(14,16));
                                    var date1 = new Date(yr1, mon1-1, dt1, hour, minute);
                                    var currentTime = new Date();
                                    if (parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                        if (date1 < currentTime) {
                                            if (date1 > vm.last_pay_date) {
                                                if (order[j].currency == client_currency) {
                                                    member_spending = member_spending + price;
                                                    var mm_comm = price*(1-commision_split/100)*client_percentage/100;
                                                    member_commission = member_commission + mm_comm;
                                                }
                                            }
                                        }
                                    }
                                    if (parseFloat(order[j].status) == 0 || parseFloat(order[j].status) == 1 || parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                        if (date1 > currentTime) {
                                            if (order[j].currency == client_currency) {
                                                member_book = member_book + price;
                                                // console.log(member_book);
                                            }
                                        }
                                    }
                                }
                            }
                            member_commission = member_commission*100;
                            vm.friendCircle[k].commission = Math.floor(member_commission)/100;
                            member_book = member_book*100;
                            vm.friendCircle[k].book = Math.floor(member_book)/100;
                            member_spending = member_spending*100; 
                            vm.friendCircle[k].spending = Math.floor(member_spending)/100;
                            console.log(member_book);
                            console.log(member_spending);
                            member_commission = 0;
                            member_spending = 0;
                            member_book = 0;
                        }
                    }
                }, function (error) {
                    console.log("error");
                });
                console.log(vm.friendCircle);
            },function(error){
                console.log("error");
            });
            $timeout(function(){
                vm.loading = false;
            }, 2500);
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

        function getPaiedEmail(accountNumber, bankCode, phone, total, lang){
            var user = JSON.parse(localStorage.getItem('user'));
            var result = {
                "name": "Client name: " + user.name,
                "phone": "Client phone: " + phone,
                "email": "Client email: " + user.email,
                "total": "Total: " + total + " Kč",
                "accountNumber": "Account number: " + accountNumber + " / " + bankCode,
                "from": user.email,
                "lang": lang + " - Client payment request"
            }
            console.log(result);

            ClientService.getPaiedEmail(result).then(function(response){
                alert(response);
                console.log(response);
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
            vm.loadingFriendRequests = true;
            ClientService.getFriendRequests().then(function(response){
                debugger;
                vm.friendRequests = response.data;
                vm.friendRequestsCount = response.data.length;
                if (vm.friendRequestsCount==0) {
                    $scope.friendRequestsCount = '';
                }
                else {
                    $scope.friendRequestsCount = '('+vm.friendRequestsCount+')';
                }
                vm.loadingFriendRequests = false;
            },function(error){
                vm.loadingFriendRequests = false;
            });
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
                vm.friendRequestsCount = response.data.length;
                if (vm.friendRequestsCount==0) {
                    $scope.friendRequestsCount = '';
                }
                else {
                    $scope.friendRequestsCount = '('+vm.friendRequestsCount+')';
                }
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
            var total_spending = 0;
            var client_spending = 0;
            var member_spending = 0;
            var commission = 0;
            var client_commission = 0;
            var member_commission = 0;
            var total_book = 0;
            var client_book = 0;
            var member_book = 0;
            var a = 0;
            vm.loading = true;
            if(vm.status){
                ClientService.getFirstMembers().then(function (response) {
                    var firstMembers = response.data;
                    vm.firstMembers = firstMembers;
                    for (var i = 0; i < firstMembers.length; i++) {
                        ClientService.getAllOrdersWithDetail(firstMembers[i]).then(function (response) {
                            var order_detail = response.data;
                            var price_sum = 0;
                            var len = order_detail.length;
                            ClientService.getClient(firstMembers[i]).then(function(response){
                                var commision_split = parseFloat(response.data.setting[0].commission_split);
                                var client_currency = response.data.setting[0].currency_short;
                                for (var i = 0; i < len; i++) {
                                    var order = order_detail[i].orders_detail;
                                    for (var j = 0; j < order.length; j++) {
                                        var client_percentage = parseFloat(order[j].menu_list.client_percentage);
                                        var price = parseInt(order[j].price);
                                        price_sum = price_sum + price;
                                        var yr1   = parseInt(order[j].serve_at.substring(0,4));
                                        var mon1  = parseInt(order[j].serve_at.substring(5,7));
                                        var dt1   = parseInt(order[j].serve_at.substring(8,10));
                                        var hour  = parseInt(order[j].serve_at.substring(11,13));
                                        var minute  = parseInt(order[j].serve_at.substring(14,16));
                                        var date1 = new Date(yr1, mon1-1, dt1, hour, minute);
                                        var currentTime = new Date();
                                        if (parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                            if (date1 < currentTime) {
                                                if (date1 > vm.last_pay_date) {
                                                    if (order[j].currency == client_currency) {
                                                        member_spending = member_spending + price;
                                                        var mm_comm = price*(1-commision_split/100)*client_percentage/100;
                                                        member_commission = member_commission + mm_comm;
                                                    }
                                                }
                                            }
                                        }
                                        if (parseFloat(order[j].status) == 0 || parseFloat(order[j].status) == 1 || parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                            if (date1 > currentTime) {
                                                if (order[j].currency == client_currency) {
                                                    member_book = member_book + price;
                                                }
                                            }
                                        }
                                    }
                                }
                                vm.member_commission = member_commission*100;
                                vm.member_commission = Math.floor(vm.member_commission)/100;
                                vm.member_spending = member_spending*100;
                                vm.member_spending = Math.floor(vm.member_spending)/100;
                                vm.member_book = member_book*100;
                                vm.member_book = Math.floor(vm.member_book)/100;
                                vm.commission = vm.client_commission + vm.member_commission;
                                vm.total_spending = vm.client_spending + vm.member_spending;
                                vm.total_book = vm.client_book + vm.member_book;
                            }, function(error){
                                console.log("error");
                            });
                        }, function (error) {
                            console.log("error");
                        });
                    }
                }, function (error){
                    console.log("error");
                });

                ClientService.getAllOrdersWithDetail(a).then(function (response) {
                    var order_detail = response.data;
                    var price_sum = 0;
                    var len = order_detail.length;
                    ClientService.getClient(a).then(function(response){
                        var commision_split = parseFloat(response.data.setting[0].commission_split);
                        var client_currency = response.data.setting[0].currency_short;
                        vm.currency = client_currency;
                        for (var i = 0; i < len; i++) {
                            var order = order_detail[i].orders_detail;
                            for (var j = 0; j < order.length; j++) {
                                var client_percentage = parseFloat(order[j].menu_list.client_percentage);
                                var price = parseInt(order[j].price);
                                price_sum = price_sum + price;
                                // console.log(client_percentage);
                                var yr1   = parseInt(order[j].serve_at.substring(0,4));
                                var mon1  = parseInt(order[j].serve_at.substring(5,7));
                                var dt1   = parseInt(order[j].serve_at.substring(8,10));
                                var hour  = parseInt(order[j].serve_at.substring(11,13));
                                var minute  = parseInt(order[j].serve_at.substring(14,16));
                                var date1 = new Date(yr1, mon1-1, dt1, hour, minute);
                                var currentTime = new Date();
                                if (parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                    if (date1 < currentTime) {
                                        if (date1 > vm.last_pay_date) {
                                            if (order[j].currency == client_currency) {
                                                client_spending = client_spending + price;
                                                var cl_comm = price*commision_split/100*client_percentage/100;
                                                client_commission = client_commission + cl_comm;
                                            }
                                        }
                                    }
                                }
                                if (parseFloat(order[j].status) == 0 || parseFloat(order[j].status) == 1 || parseFloat(order[j].status) == 2 || parseFloat(order[j].status) == 4) {
                                    if (date1 > currentTime) {
                                        if (order[j].currency == client_currency) {
                                            client_book = client_book + price;
                                        }
                                    }
                                }
                            }
                        }
                        vm.client_commission = client_commission*100;
                        vm.client_commission = Math.floor(vm.client_commission)/100;
                        vm.client_spending = client_spending*100;
                        vm.client_spending = Math.floor(vm.client_spending)/100;
                        vm.client_book = client_book*100;
                        vm.client_book = Math.floor(vm.client_book)/100;
                        vm.commission = vm.client_commission + vm.member_commission;
                        vm.total_spending = vm.client_spending + vm.member_spending;
                        vm.total_book = vm.client_book + vm.member_book;
                    }, function(error){
                        console.log("error");
                    });
                }, function (error) {
                    console.log("error");
                });

                vm.commission = vm.client_commission + vm.member_commission;
                vm.total_spending = vm.client_spending + vm.member_spending;
                vm.total_book = vm.client_book + vm.member_book;
                ClientService.getClient_payment().then(function (response) {
                    vm.order_payment = response.client_payments;
                    vm.total = 0;
                    vm.client = 0;
                    vm.member = 0;
                    if(vm.order_payment == undefined){var len = 0;}
                    else{
                    var len = vm.order_payment.length;}
                    for (var i = 0; i < len; i++) {

                        vm.client = vm.client + parseFloat(vm.order_payment[i].own_remuneration);
                        vm.member = vm.member + parseFloat(vm.order_payment[i].member_remuneration);
                        vm.order_payment[i].own_remuneration = parseFloat(vm.order_payment[i].own_remuneration) + parseFloat(vm.order_payment[i].member_remuneration);
                        vm.order_payment[i].own_remuneration = vm.order_payment[i].own_remuneration*100;
                        vm.order_payment[i].own_remuneration = Math.floor(vm.order_payment[i].own_remuneration)/100;
                    }
                    vm.total = (vm.member + vm.client)*100;
                    vm.total = Math.floor(vm.total)/100;
                    vm.client = vm.client*100;
                    vm.client = Math.floor(vm.client)/100;
                    vm.member = vm.member*100;
                    vm.member = Math.floor(vm.member)/100;
                }, function (error) {
                    console.log("error");
                });
            }
            $timeout(function(){
                vm.loading = false;
            }, 2500);
        }

        function getbBookings() {
            debugger;
            vm.loading = true;
            ClientService.getOrders(vm.currentPage).then(function (response) {
                debugger;
                vm.orders = response.data;
                // console.log(vm.orders);
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
                    console.log("success");
                    vm.myDetailsSuccess = "Changes have been successfully saved!";
                    vm.myDetails.password = "";
                    vm.myDetails.confirm_password = "";
                    window.scrollTo(0, 0);
                },function(error){
                    console.log("error");
                });
            }
        }

        function closeAlert() {
            vm.myDetailsSuccess = "";
            vm.location_error = "";
        }
    }

})();
