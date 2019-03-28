let socket;
let prefix = '';
$(function () {
    $.fn.button = function (action) {
        if (action === 'loading' && this.data('loading-text')) {
            this.data('original-text', this.html()).html(this.data('loading-text')).prop('disabled', true);
        }
        if (action === 'reset' && this.data('original-text')) {
            this.html(this.data('original-text')).prop('disabled', false);
        }
    };

    socket = io('http://10.100.3.20:2020');
    socket.on('connect', function () {
        console.log('connected');
    });
    socket.on('user_results_client', function () {
        userResults();
    });


    checkUser();
    $("#signButton").on('click', sign);
    $("#sliderDiv")
        .on('click', '.save-question-button', saveQuestion)
        .on('click', '.answers', function () {
            $(this).parent().find('.active').removeClass('active');
            $(this).addClass('active');
        });
});

function saveQuestion() {
    let $slider = $("#sliderDiv");
    let $active = $slider.find('.carousel-item.active');

    if ($active) {
        let params = {
            'question_id': $active.data('id'),
            'answer_id': $active.find('.list-group-item.active').data('id')
        };
        if (!params.answer_id > 0) {
            $active.find('.help').show();
            return;
        }
        $active.find('button').button('loading');
        $.ajax({
            url: prefix + "/save-answer",
            data: params,
            dataType: 'json',
            type: "POST"
        }).done(function (response) {
            $active.find('button').button('reset');
            if (response != undefined) {
                if (response.status === true) {
                    socket.emit('answer');
                    nextQuestion();
                } else if (response.status === false || response.status == 'error') {
                    alert(response.error);
                } else {
                    alert('Ошибка запроса. Обратитесь к администратору.');
                }
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        }).fail(function () {
            alert('Возникла ошибка с сохранением результатов. Попробуйте позже.');
            $active.find('button').button('reset');
        });
    }
}

function nextQuestion() {
    let $slider = $("#sliderDiv");
    if ($slider.find('.carousel-item.active').next().data('id') === undefined) {
        userResults();
    }
    $("#sliderDiv").carousel('next');
}

function checkUser() {
    $("#userName").attr('disabled', true);
    $.ajax({
        url: prefix + "/user",
        data: {},
        dataType: 'json',
        type: "GET"
    }).done(function (response) {
        if (response != undefined) {
            if (response.status === true) {
                if (response.data && parseInt(response.data.id) > 0) {
                    $("#userName").val(response.data.name);
                    $("#userTitle").text('Приветствую Вас, ' + response.data.name + '!').show();
                    if (response.data.done == 1) {
                        let $slider = $("#sliderDiv");
                        $slider.find('.active').removeClass('active');
                        $("#endForm").addClass('active');
                        userResults();
                    } else {
                        gotoQuestions();
                    }
                }
                $("#userName").attr('disabled', false);
            } else if (response.status === false || response.status == 'error') {
                alert(response.error);
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        } else {
            alert('Ошибка запроса. Обратитесь к администратору.');
        }
    });
}

function gotoQuestions() {
    $.ajax({
        url: prefix + "/questions",
        data: {},
        dataType: 'json',
        type: "GET"
    }).done(function (response) {
        if (response != undefined) {
            if (response.status === true) {
                let $slider = $("#sliderDiv");
                $slider.find('.carousel-item[data-id]').remove();
                let $sliderBody = $("#endForm");
                $.each(response.data, function (key, question) {
                    let html = '<div data-id="' + question['id'] + '" class="carousel-item lead"><h3><small>Вопрос:</small> ' + this['name'] + '</h3><h5>Варианты ответов:</h5><div class="list-group">';
                    $.each(question['answers'], function (key, answer) {
                        // html += '<div class="form-check"><input class="form-check-input" type="radio" value="' + answer['id'] + '" id="answer' + answer['id'] + '" name="answer' + question['id'] + '">' +
                        //     '  <label class="form-check-label" for="answer' + answer['id'] + '">' + answer['name'] + '</label></div>'
                        html += '<a href="javascript: void(0)" class="list-group-item list-group-item-action answers" data-id="' + answer['id'] + '">' + answer['name'] + '</a>'
                    });
                    html += '</div><br /><button class="btn btn-lg btn-success save-question-button" role="button" data-loading-text="Сохранение...">Ответить</button></div>';
                    $sliderBody.before(html);
                });
                $slider.carousel(1);
            } else if (response.status === false || response.status == 'error') {
                alert(response.error);
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        } else {
            alert('Ошибка запроса. Обратитесь к администратору.');
        }
    });
}

function sign() {
    let $dom = $("#userName");
    let $eduDom = $("#eduPlace");
    let name = $dom.val();
    let edu = $eduDom.val();

    if (name == '') {
        $dom.addClass('is-invalid');
        return false;
    }
    if (edu == '') {
        $eduDom.addClass('is-invalid');
        return false;
    }
    $dom.removeClass('is-invalid');
    $.ajax({
        url: prefix + "/sign",
        data: {
            name: name,
            edu: edu
        },
        dataType: 'json',
        type: "POST"
    }).done(function (response) {
        if (response != undefined) {
            if (response.status === true) {
                gotoQuestions();
            } else if (response.status === false || response.status == 'error') {
                alert(response.error);
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        } else {
            alert('Ошибка запроса. Обратитесь к администратору.');
        }
    });
}

function startNew() {
    if (confirm('Пройти тест еще раз?')) {
        $.ajax({
            url: prefix + "/restart",
            data: {},
            dataType: 'json',
            type: "GET"
        }).done(function (response) {
            if (response != undefined) {
                if (response.status === true) {
                    gotoQuestions();
                } else if (response.status === false || response.status == 'error') {
                    alert(response.error);
                } else {
                    alert('Ошибка запроса. Обратитесь к администратору.');
                }
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        });
    }
}

function userResults() {
    $("#resultBtn").button('loading');
    $.ajax({
        url: prefix + "/user-result",
        data: {},
        dataType: 'json',
        type: "GET"
    }).done(function (response) {
        if (response != undefined) {
            if (response.status === true) {
                if (response.data === false) {
                    $("#resultDiv").html('В обработке... Ожидайте');
                    $("#startNewBtn").hide();
                    $("#resultBtn").show();
                } else {
                    $("#startNewBtn").show();
                    $("#resultBtn").hide();

                    let html = '<p class="lead text-success">Ниже представлен перечень заинтересовавших Вас специальностей в порядке убывания приоритета</p>';
                    let ballSize = {
                        ball5: '1',
                        ball4: '1',
                        ball3: '2',
                        ball2: '3',
                        ball1: '5',
                    };
                    $.each(response.data, function () {
                        if (this.data > 0) {
                            html += '<h' + ballSize['ball' + this.data] + '>' + this.name + '</h' + ballSize['ball' + this.data] + '>';
                        }
                    });
                    html += '</ol>';
                    $("#resultDiv").html(html);
                }
            } else if (response.status === false || response.status == 'error') {
                alert(response.error);
            } else {
                alert('Ошибка запроса. Обратитесь к администратору.');
            }
        } else {
            alert('Ошибка запроса. Обратитесь к администратору.');
        }
    }).always(function () {
        $("#resultBtn").button('reset');
    });
}