$(document).ready(function () {
    var test_platform_count = 10;
    for (var counter = 1; counter <= test_platform_count; counter++) {
        var url = 'http://wp-test' + counter + '.prelive01.mcemcw.com';
        $('.preliveLink').append('<span>' + 'wp-test' + counter + '</span>');
        $('.preliveLink').append('<p class="success">' + url + '</p>');
    }

    const preliveUrl = '/jiraIssues.php';
    axios.get(preliveUrl)
        .then(function (response) {
            var jiraResponse = response.data;
            var finded = false;
            function getMatch(array_data) {
                $('.preliveLink').empty('<p class="success"> </p>');
                for (var counter = 1; counter <= test_platform_count; counter++) {
                    var url = 'http://wp-test' + counter + '.prelive01.mcemcw.com';
                    if (counter == 6) {
                        $('.preliveLink').append('<span>' + 'wp-test' + counter + '</span>');
                        $('.preliveLink').append('<p class="danger">' + '<a href="' +
                            url + '">' + url + '</a>' + '</p>');
                        $('.domainName').append('<p class="danger">grademiners.com</p>');
                        $('.developer').append('<p class="danger"></p>');
                        $('.datePush').append('<p class="danger"></p>');
                        continue;
                    }
                    if (counter == 10) {
                        $('.preliveLink').append('<span>' + 'wp-test' + counter + '</span>');
                        $('.preliveLink').append('<p class="danger">' + '<a href="' +
                            url + '">' + url + '</a>' + '</p>');
                        $('.domainName').append('<p class="danger">masterpapers.com</p>');
                        $('.developer').append('<p class="danger"></p>');
                        $('.datePush').append('<p class="danger"></p>');
                        continue;
                    }
                    for(var i = 0; i < array_data.length; i++) {

                        if (url == array_data[i]["prelive_number"]) {
                            finded = true;
                            $('.preliveLink').append('<span>' + 'wp-test' + counter + '</span>');
                            $('.preliveLink').append('<p class="danger">' + '<a href="'
                                + array_data[i]["prelive_number"] +'">' + array_data[i]["prelive_number"]
                                + '</a>'+ '</p>');
                            $('.domainName').append('<p class="danger">' + array_data[i]["domain"] + '</p>');
                            $('.developer').append('<p class="danger">' + array_data[i]["developer"] + '</p>');
                            $('.datePush').append('<p class="danger' + counter +'">' + endTime + '</p>');
                            var endTime = new Date(array_data[i]["date"]);
                            var pageTimer =  document.querySelector('.danger' + counter);
                            var counterStr = (ts) => pageTimer.innerHTML =
                                `${ts.days} д. ${ts.hours} ч. ${ts.minutes} м.`;
                            countdown((ts) => counterStr(ts), endTime);
                            break;
                        }
                    }
                    if (!finded) {
                        $('.preliveLink').append('<span>' + 'wp-test' + counter + '</span>');
                        $('.preliveLink').append('<p class="danger">' + '<a href="' +
                            url + '">' + url + '</a>' + '</p>');
                        $('.domainName').append('<p class="success">Free</p>');
                        $('.developer').append('<p class="success"></p>');
                        $('.datePush').append('<p class="success"></p>');
                    }
                    finded = false;
                }
            }
            getMatch(jiraResponse);
        }.bind(this))
        .catch(function (error) {
            console.log(error);
        });
});