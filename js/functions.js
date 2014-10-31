$(document).ready(function ()
{
    getTop10UserGames();

    
    var headerProfile = ' ';
    var imageProfile = ' ';
    var loggedIn = 1;
    if("userID" in localStorage)
    {
        loggedIn = 0;
    }

    function checkSurveyOptions()
    {
        $.post( SERVER + "/WebServices/checkSurvey.php",
        {
            
        }, function (data, status)
        {

            var jsonObj = data;            
            if (jsonObj.code == 0)
            {
                
                var surveyData = jsonObj.response;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    for(var j = 1 ; j <= 10 ; j++)
                    {
                        //alert('#a'+ parseInt(j) + surveyData[i].id);
                        if($('#a'+ parseInt(j) + surveyData[i].id).val() == surveyData[i].value )
                        {
                            //alert(surveyData[i].value);
                            $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true ).checkboxradio( "refresh" );
                        }
                    }

                }                                                          
            }
            

            
        });
    };

    function getTop10UserGames()
    {
        
        $( "#TopHeader" ).show();
        if("userID" in localStorage)
        {
            currentPage = "#home";
            //pushCurrentPage(currentPage);
            //showModal();

            var ID = localStorage.getItem("ID");
            $('#mainTop10').html(' ');
            $('#TopGamesHeadingHome').html('Your Hot 10 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getUserTopGames.php",
            {
                userID: ID,
                limit: 10
            }, function (data, status)
            {
                var jsonObj = data;
                var game = '';

                if (jsonObj.code == 0)
                {        
                    //printSurvey();    
                    $.post( SERVER + "/WebServices/checkSurvey.php",
                    {
                        
                    }, function (data, status)
                    {

                        var jsonObj = data;            
                        if (jsonObj.code == 0)
                        {
                            
                            var surveyData = jsonObj.response;
                            for (var i = 0; i < jsonObj.rowCount; i++)
                            {
                                for(var j = 1 ; j <= 10 ; j++)
                                {
                                    //alert('#a'+ parseInt(j) + surveyData[i].id);
                                    if($('#a'+ parseInt(j) + surveyData[i].id).val() == surveyData[i].value )
                                    {
                                        //alert(surveyData[i].value);
                                        $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true );
                                    }
                                }

                            }                                                          
                        }
                        

                        
                    });

                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        //div_img = '';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    //hideModal();
                    $( ".gamePanel" ).show();
                    $( ".moviePanel" ).hide();
                    $( ".gameHeader" ).show();
                    $( ".movieHeader" ).hide();
                    $('#mainTop10').html(game);
                    $('#mainTop10').listview('refresh');

                    // $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Survey</a>');
                    // $('#surveyInPanel').listview('refresh');                    
                }
                else if (jsonObj == -1)
                {
                    //hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    //hideModal();
                    refreshHome();
                }
            });
        }
        else
        {
            currentPage = "#home";
            //pushCurrentPage(currentPage);
            showModal();
            $('#mainTop10').html(' ');
            $('#TopGamesHeadingHome').html('Top 10 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getTopTenGames.php",
            {
                platform: 'all'
            }, function (data, status)
            {

                var jsonObj = data;
                var game = '';
                if (jsonObj.code == 0)
                {
                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        //div_img = '';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    hideModal();
                   // printSurvey();
                    $( ".gamePanel" ).show();
                    $( ".moviePanel" ).hide();
                    $( ".gameHeader" ).show();
                    $( ".movieHeader" ).hide();

                    $('#mainTop10').html(game);
                    $('#mainTop10').listview('refresh');
                    
                    $('#surveyInPanel').html('');
                    $('#surveyInPanel').listview('refresh');

                }
                else if (jsonObj == -1)
                {
                    hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#mainTop10').html("No Game Found");
                }
            });
        }
    };

    

    
    function getTopTenGames(category, type)
    {
        if(category != 'all')
        {
            $( "#TopHeader" ).show();
            currentPage = "#top-10";
            showModal();
            $('#TopGamesHeading').html('Top 10 ' + type + ' Games');
            $('#top10Games').html(' ');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getTopTenGames.php",
            {
                platform: category
            }, function (data, status)
            {
                var jsonObj = data;
                var game = '';
                if (jsonObj.code == 0)
                {

                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    hideModal();
                    $('#top10Games').html(game);
                    $('#top10Games').listview('refresh');
                }
                else if (jsonObj == -1)
                {
                    hideModal();
                    $('#top10Games').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#top10Games').html("No Game Found");
                }
            });
        }
        else
        {
            if("userID" in localStorage)
            {
                currentPage = "#top-10";
                //pushCurrentPage(currentPage);
                //showModal();
                var ID = localStorage.getItem("ID");
                $('#top10Games').html(' ');
                $('#TopGamesHeading').html('Your Hot 10 Games');
                var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
                $.post( SERVER + "/WebServices/getUserTopGames.php",
                {
                    userID: ID,
                    limit: 10
                }, function (data, status)
                {
                    var jsonObj = data;
                    var game = '';

                    if (jsonObj.code == 0)
                    {    
                        var gameData = jsonObj.response;
                        for (var i = 1; i <= jsonObj.rowCount; i++)
                        {
                            li = '<li data-icon="false">';
                            a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                            //div_img = '';
                            img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                            span1 = '<span class="discrip">' + i + '</span></div>';
                            h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                            p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                            p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                            var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                            if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                            {
                                div = '<div class="meter-full">';
                                thermometer = '<div class="thermometer"></div></div></a></li>';
                            }
                            else
                            {
                                div = '<div class="meter">';
                                thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                            }
                            span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                            game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                        }
                        //hideModal();
                        // $( ".gamePanel" ).show();
                        // $( ".moviePanel" ).hide();
                        // $( ".gameHeader" ).show();
                        // $( ".movieHeader" ).hide();
                        $('#top10Games').html(game);
                        $('#top10Games').listview('refresh');

                        // $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Survey</a>');
                        // $('#surveyInPanel').listview('refresh');                    
                    }
                    else if (jsonObj == -1)
                    {
                        //hideModal();
                        $('#top10Games').html("DB Connectivity Failed");
                    }
                    else
                    {
                        $('#top10Games').html(' ');
                        $('#TopGamesHeading').html('Top 10 Games');
                        $.post( SERVER + "/WebServices/getTopTenGames.php",
                        {
                            platform: category
                        }, function (data, status)
                        {
                            var jsonObj = data;
                            var game = '';
                            if (jsonObj.code == 0)
                            {

                                var gameData = jsonObj.response;
                                for (var i = 1; i <= jsonObj.rowCount; i++)
                                {
                                    li = '<li data-icon="false">';
                                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                                    span1 = '<span class="discrip">' + i + '</span></div>';
                                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                                    {
                                        div = '<div class="meter-full">';
                                        thermometer = '<div class="thermometer"></div></div></a></li>';
                                    }
                                    else
                                    {
                                        div = '<div class="meter">';
                                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                                    }
                                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                                }
                                hideModal();
                                $('#top10Games').html(game);
                                $('#top10Games').listview('refresh');
                            }
                            else if (jsonObj == -1)
                            {
                                hideModal();
                                $('#top10Games').html("DB Connectivity Failed");
                            }
                            else
                            {
                                hideModal();
                                $('#top10Games').html("No Game Found");
                            }
                        });
                    }
                });
            }
            else
            {
                currentPage = "#home";
                //pushCurrentPage(currentPage);
                showModal();
                $('#top10Games').html(' ');
                $('#TopGamesHeading').html('Top 10 Games');
                var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
                $.post( SERVER + "/WebServices/getTopTenGames.php",
                {
                    platform: 'all'
                }, function (data, status)
                {

                    var jsonObj = data;
                    var game = '';
                    if (jsonObj.code == 0)
                    {
                        var gameData = jsonObj.response;
                        for (var i = 1; i <= jsonObj.rowCount; i++)
                        {
                            li = '<li data-icon="false">';
                            a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                            //div_img = '';
                            img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                            span1 = '<span class="discrip">' + i + '</span></div>';
                            h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                            p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                            p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                            var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                            if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                            {
                                div = '<div class="meter-full">';
                                thermometer = '<div class="thermometer"></div></div></a></li>';
                            }
                            else
                            {
                                div = '<div class="meter">';
                                thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                            }
                            span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                            game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                        }
                        hideModal();
                       // printSurvey();
                        // $( ".gamePanel" ).show();
                        // $( ".moviePanel" ).hide();
                        // $( ".gameHeader" ).show();
                        // $( ".movieHeader" ).hide();

                        $('#top10Games').html(game);
                        $('#top10Games').listview('refresh');
                        
                        $('#surveyInPanel').html('');
                        $('#surveyInPanel').listview('refresh');

                    }
                    else if (jsonObj == -1)
                    {
                        hideModal();
                        $('#top10Games').html("DB Connectivity Failed");
                    }
                    else
                    {
                        hideModal();
                        $('#top10Games').html("No Game Found");
                    }
                });
            }
        }
    };
    $(document).on("click", "#barcode", function ()
    {
        var scanner = cordova.plugins.barcodeScanner;
        scanner.scan(function (result)
        {
            alert("We got a barcode\n" + "Result: " + result.text + "\n" + "Format: " + result.format + "\n" + "Cancelled: " + result.cancelled);
        }, function (error)
        {
            alert("Scanning failed: " + error);
        });
    });
    $(document).on("click", ".GetGames", function ()
    {
        if(currentPage != '#top-10')
            pushCurrentPage(currentPage);
        getTopTenGames($(this).attr("id"), $(this).attr("value"));
    });

    function showGameDetails(post_id)
    {
        $( "#TopHeader" ).show();
        currentPage = "#game-detail";
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        $('#showDetails').html(' ');
        $.post( SERVER + "/WebServices/getDetailsOfGame.php",
        {
            id: post_id
        }, function (data, status)
        {
            var jsonObj = data;
            var details = '';
            if (jsonObj.code == 0)
            {
                var gameDetails = jsonObj.response;
                div1 = '<div class="head">';
                h2 = '<h2>' + gameDetails[0].title + '</h2>';
                p1 = '<p class="c-name">posted by: ' + gameDetails[0].postAuthor + ' ' + gameDetails[0].postDate + '</p></div>';
                div2 = '<div class="main-news">';
                div3 = '<div class="big-news">';
                a1 = '<a href="">';
                img = '<img src="' + gameDetails[0].img + '" alt=""></a></div></div>';
                div4 = '<div class="info">';
                p2 = '<p>' + gameDetails[0].consoles + '</p>';
                p3 = '<p>Release Date: ' + gameDetails[0].releaseDate + '</p>';
                p4 = '<p>' + gameDetails[0].company + '</p>';
                p5 = '<p>' + gameDetails[0].esrb_rating + '</p></div>';
                div5 = '<div class="detail">';
                p6 = '<p>' + gameDetails[0].content + '</p></div>';
                div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';
                details = div1 + h2 + p1 + div2 + div3 + a1 + img + div4 + p2 + p3 + p4 + p5 + div5 + p6 + div6;
                hideModal();
                $('#showDetails').html(details);
                $('#showDetails').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#showDetails').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#showDetails').html("No Game Details Found");
            }
        });
    };
    $(document).on("click", ".GetPostID", function ()
    {
        if(currentPage != '#game-detail')
            pushCurrentPage(currentPage);
        showGameDetails($(this).attr("value"));
    });
    /******************************/
    function getTopNews(category, type)
    {
        $( "#TopHeader" ).show();
        currentPage = '#news';
        showModal();
        $('#mainNews').html(' ');
        $('#moreNews').html(' ');
        $('#otherNews').html(' ');
        $.post( SERVER + "/WebServices/getNews.php",
        {
            platform: category
        }, function (data, status)
        {
            var jsonObj = data;
            var top3News = '';
            var subNews = '';
            if (jsonObj.code == 0)
            {
                var newsData = jsonObj.response;
                var div1, a1, img1, span1, p1, p2, p3, div2, div3, a2, img2, span2, p4, p5, p6, div4, a3, img3, span3, p7, p8, p9, index;
                if (jsonObj.rowCount >= 2)
                {
                    div1 = '<div class="big-news">';
                    a1 = '<a class="GetNewsID" href="#news-detail" value="' + newsData[0].postID + '">';
                    img1 = '<img src="' + newsData[0].img + '" alt="">';
                    span1 = '<span class="news-discrip">';
                    p1 = '<p><strong>' + newsData[0].title + '</strong></p>';
                    p2 = '<p class="sm limitText">' + newsData[0].content + '</p>';
                    p3 = '<p class="ex-sm">' + newsData[0].postDate + ' | ' + newsData[0].postAuthor + '</p></span></a></div>';
                    top3News = div1 + a1 + img1 + span1 + p1 + p2 + p3;
                    index = 1;
                }
                if (jsonObj.rowCount >= 4)
                {
                    div2 = '<div class="small-news">';
                    div3 = '<div class="news"style="margin-right: .5%;">';
                    a2 = '<a class="GetNewsID" href="#news-detail" value="' + newsData[1].postID + '">';
                    img2 = '<img src="' + newsData[1].img + '" alt="">';
                    span2 = '<span class="news-discrip">';
                    p4 = '<p><strong>' + newsData[1].title + '</strong></p>';
                    p5 = '<p class="sm limitText">' + newsData[1].content + '</p>';
                    p6 = '<p class="ex-sm">' + newsData[1].postDate + ' | ' + newsData[1].postAuthor + '</p></span></a></div>';
                    top3News = div1 + a1 + img1 + span1 + p1 + p2 + p3 + div2 + div3 + a2 + img2 + span2 + p4 + p5 + p6 + '</div>';
                    index = 2;
                }
                if (jsonObj.rowCount > 5)
                {
                    div4 = '<div class="news"style="margin-left: .5%;">';
                    a3 = '<a class="GetNewsID" href="#news-detail" value="' + newsData[2].postID + '">';
                    img3 = '<img src="' + newsData[2].img + '" alt="">';
                    span3 = '<span class="news-discrip">';
                    p7 = '<p><strong>' + newsData[2].title + '</strong></p>';
                    p8 = '<p class="sm limitText">' + newsData[2].content + '</p>';
                    p9 = '<p class="ex-sm">' + newsData[2].postDate + ' | ' + newsData[2].postAuthor + '</p></span></a></div></div>';
                    top3News = div1 + a1 + img1 + span1 + p1 + p2 + p3 + div2 + div3 + a2 + img2 + span2 + p4 + p5 + p6 + div4 + a3 + img3 + span3 + p7 + p8 + p9;
                    index = 3;
                }
                $('#TopNewsHeading').html('Top News Stories: ' + type);
                $('#mainNews').html(top3News);
                var li, details, thumbnail, span4, heading, content, nameDate;
                for (var i = index; i < (jsonObj.rowCount); i++)
                {
                    li = '<li data-icon="false">';
                    details = '<a class="GetNewsID" href="#news-detail" value="' + newsData[i].postID + '">';
                    thumbnail = '<div class="li-img"><img src="' + newsData[i].img + '">';
                    span4 = '<span class="discrip">' + parseInt(i - 2) + '</span></div>';
                    heading = '<div class="li-detail"><h2>' + newsData[i].title + '</h2>';
                    content = '<p class="limitText">' + newsData[i].content + '</p>';
                    nameDate = '<p class="c-name">' + newsData[i].postDate + ' | ' + newsData[i].postAuthor + '</p></div></a></li>';
                    subNews = subNews + li + details + thumbnail + span4 + heading + content + nameDate;
                }
                $('#moreNews').html("More News Stories");
                $('#otherNews').html(subNews);
                $('#otherNews').listview('refresh');
                hideModal();
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#mainNews').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#mainNews').html("No News Found");
            }
        });
    };
    $(document).on("click", ".GetNews", function ()
    {
        if(currentPage != '#news')
            pushCurrentPage(currentPage);
        getTopNews($(this).attr("id"), $(this).attr("value"));
    });

    function showNewsDetails(post_id)
    {
        $( "#TopHeader" ).show();
        currentPage = "#news-detail";
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        $('#showNewsDetails').html(' ');
        $.post( SERVER + "/WebServices/getDetailsOfNews.php",
        {
            id: post_id
        }, function (data, status)
        {
            var jsonObj = data;
            var details = '';
            if (jsonObj.code == 0)
            {
                var newsDetails = jsonObj.response;
                div1 = '<div class="head">';
                h2 = '<h2>' + newsDetails[0].title + '</h2>';
                p1 = '<p class="c-name">posted by: ' + newsDetails[0].postAuthor + ' ' + newsDetails[0].postDate + '</p></div>';
                div2 = '<div class="main-news">';
                div3 = '<div class="big-news">';
                a1 = '<a href="">';
                img = '<img src="' + newsDetails[0].img + '" alt=""></a></div></div>';
                div4 = '<div class="info">';
                p2 = '<p>';
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    if (i != (jsonObj.rowCount - 1))
                    {
                        p2 = p2 + newsDetails[0].categories[i] + ',';
                    }
                    else
                    {
                        p2 = p2 + newsDetails[0].categories[i] + '</p>';
                    }
                }
                div5 = '</div><div class="detail">';
                p3 = '<p>' + newsDetails[0].content + '</p></div>';
                div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';
                details = div1 + h2 + p1 + div2 + div3 + a1 + img + div4 + p2 + div5 + p3 + div6;
                hideModal();
                $('#showNewsDetails').html(details);
                $('#showNewsDetails').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#showNewsDetails').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#showNewsDetails').html("No Game Details Found");
            }
        });
    };
    $(document).on("click", ".GetNewsID", function ()
    {
        if(currentPage != '#news-detail')
            pushCurrentPage(currentPage);
        showNewsDetails($(this).attr("value"));
    });

    $(document).on("click", "#allPlatforms", function ()
    {
        if(currentPage != '#platforms')
            pushCurrentPage(currentPage);
        currentPage = '#platforms';
        $( "#TopHeader" ).show();
    });

    function getMeltingPoint()
    {
        $( "#TopHeader" ).show();
        currentPage = "#meltingpoint";
        showModal();
        $('#mps').html(' ');
        var li, a, h2, p1, p2;
        $.post( SERVER + "/WebServices/getMeltingPointStories.php",
        {
            platform: "fjsdk"
        }, function (data, status)
        {
            var jsonObj = data;
            var meltingpoints = '';
            if (jsonObj.code == 0)
            {
                var mpData = jsonObj.response;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetMP" href="#mp-detail" value="' + mpData[i].postID + '">';
                    h2 = '<h2>' + mpData[i].title + '</h2>';
                    p1 = '<p>' + mpData[i].content + '</p>';
                    p2 = '<p class="c-name">' + mpData[i].postDate + ' | ' + mpData[i].postAuthor + '</p></a></li>';
                    meltingpoints = meltingpoints + li + a + h2 + p1 + p2;
                }
                hideModal();
                $('#mps').html(meltingpoints);
                $('#mps').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#mps').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#mps').html("No Game Found");
            }
        });
    };
    $(document).on("click", "#meltingp", function ()
    {
        if(currentPage != '#meltingpoint')
            pushCurrentPage(currentPage);
        getMeltingPoint();
    });

    function showMPStory(post_id)
    {
        $( "#TopHeader" ).show();
        currentPage = "#mp-detail";
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        $('#showMPDetails').html(' ');
        $.post( SERVER + "/WebServices/getMeltingPointStoryDetail.php",
        {
            id: post_id
        }, function (data, status)
        {
            var jsonObj = data;
            var details = '';
            if (jsonObj.code == 0)
            {
                var mpDetails = jsonObj.response;
                div1 = '<div class="head">';
                h2 = '<h2>' + mpDetails[0].title + '</h2>';
                p1 = '<p class="c-name">posted by: ' + mpDetails[0].postAuthor + ' ' + mpDetails[0].postDate + '</p></div>';
                div2 = '<div class="main-news">';
                div3 = '<div class="big-news">';
                a1 = '<a href=""></a></div></div>';
                div4 = '<div class="info">';
                div5 = '</div><div class="detail">';
                p3 = '<p>' + mpDetails[0].content + '</p></div>';
                div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';
                details = div1 + h2 + p1 + div2 + div3 + a1 + div4 + div5 + p3 + div6;
                hideModal();
                $('#showMPDetails').html(details);
                $('#showMPDetails').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#showMPDetails').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#showMPDetails').html("No Game Details Found");
            }
        });
    };
    $(document).on("click", ".GetMP", function ()
    {
        if(currentPage != '#mp-detail')
            pushCurrentPage(currentPage);
        showMPStory($(this).attr("value"));
    });

    var catType = 'all';

    function showSearchResults(category)
    {
        $( "#TopHeader" ).show();
        showModal();
        catType = category;
        $('#searchResults').html(' ');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/searchGames.php",
        {
            platform: category
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '</div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                hideModal();
                $('#searchResults').html(game);
                $('#searchResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#searchResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#searchResults').html("No Game Found");
            }
        });
    };
    $(document).on("click", ".searchCategory", function ()
    {

        if(currentPage != '#search')
        {
            currentPage = '#search';
            pushCurrentPage(currentPage);
        }
        showSearchResults($(this).attr("value"));
    });

    function showSearchResultsByApha(alpha)
    {
        $( "#TopHeader" ).show();
        showModal();
        $('#searchResults').html(' ');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/searchGames.php",
        {
            platform: catType,
            alphabet: alpha
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '</div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                hideModal();
                $('#searchResults').html(game);
                $('#searchResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#searchResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#searchResults').html("No Game Found");
            }
        });
    };
    $(document).on("click", ".searchCatAlpha", function ()
    {
        showSearchResultsByApha($(this).text());
    });
    $(document).on("click", "#searchAll", function ()
    {
        if(currentPage != '#search')
        {
            currentPage = '#search';
            pushCurrentPage(currentPage);
        }
        showSearchResults('all');
    });

    function showForums()
    {
        $( "#TopHeader" ).show();
        currentPage = '#forums';
        showModal();
        $('#forumsList').html(' ');
        var li, a, h2;
        $.post( SERVER + "/WebServices/getForumCategories.php",
        {}, function (data, status)
        {
            var jsonObj = data;
            var forum = '';
            if (jsonObj.code == 0)
            {
                var forumData = jsonObj.response;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetForumID" id="' + forumData[i].title + '" href="#fourm-topic" value="' + forumData[i].postID + '">';
                    h2 = '<h2>' + forumData[i].title + '</h2></a></li>';
                    forum = forum + li + a + h2;
                }
                hideModal();
                $('#forumsList').html(forum);
                $('#forumsList').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#forumsList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#forumsList').html("No Forum Found");
            }
        });
    };
    $(document).on("click", "#showForums", function ()
    {
        if(currentPage != '#forums')
            pushCurrentPage(currentPage);
        showForums();
    });

    function showForumTopics(forumId, name)
    {
        $( "#TopHeader" ).show();
        currentPage = '#fourm-topic';
        showModal();
        $('#ForumHeading').html(name);
        $( "#createTopic" ).attr( "value", forumId );
        $('#topics').html(' ');
        var tr, td1, td2, td3, td4;
        $.post( SERVER + "/WebServices/getForumTopics.php",
        {
            id: forumId
        }, function (data, status)
        {
            var jsonObj = data;
            var forum = '';
            if (jsonObj.code == 0)
            {
                var forumData = jsonObj.response;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    tr = '<li data-icon="false">';
                    td1 = '<a class="GetForumPosts" id="' + forumData[i].title + '" value="' + forumData[i].postID + '" href="#fourm-storie">';
                    td2 = '<h2>' + forumData[i].title + '</h2>';
                    td3 = '<p class="c-name">' + forumData[i].last_active_time + '</p>';
                    td4 = '<span class="ui-li-count">' + forumData[i].comment_count + ' Post</span></a></li>';

                    forum = forum + tr + td1 + td2 + td3 + td4;
                }
                hideModal();

                $('#topics').html(forum);
                $('#topics').listview('refresh');
                
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#topics').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#topics').html("No Topic Found");
            }
        });
    };
    $(document).on("click", ".GetForumID", function ()
    {
        if("userID" in localStorage)
        {
            $( "#topicArea" ).show();
            $( "#sign-in-first" ).hide();
        }
        else 
        {
            $( "#topicArea" ).hide();
            $( "#sign-in-first" ).show();                  
        }
        
        pushCurrentPage(currentPage);
        showForumTopics($(this).attr("value"), $(this).attr("id"));
    });

    function showForumPosts(postId, name)
    {
        $( "#TopHeader" ).show();
        currentPage = '#fourm-storie';
        showModal();
        $('#TopicHeading').html(name);
        $( "#sendReply" ).attr( "value", postId );
        $('#forum-posts').html(' ');
        var li, a, img, h2, p1, p2, div, span;
        $.post( SERVER + "/WebServices/getTopicReplies.php",
        {
            id: postId
        }, function (data, status)
        {
            var jsonObj = data;
            var forum = '';
            var commentArea = '';

            if (jsonObj.code == 0)
            {
                var postData = jsonObj.response;
                
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a href="#">';
                    img = '<img class="li-img" src="' + postData[i].img + '">';
                    h2 = '<h2>' + postData[i].display_name + '</h2>';
                    p1 = '<p>' + postData[i].content + '</p>';
                    p2 = '<p class="c-name">' + postData[i].postDate + '</p>';
                    div = '<div class="meter-full">';
                    span = '<span class="ui-li-count margin-right">#' + postData[i].postID + '</span></div></a></li>';
                    forum = forum + li + a + img + h2 + p1 + p2 + div + span;
                }
                hideModal();                

                $('#forum-posts').html(forum);
                $('#forum-posts').listview('refresh');
                $('#replyHeading').html('Reply to: ' + postData[0].display_name );

            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#forum-posts').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#forum-posts').html("No Posts Found");
            }
        });
    };
    $(document).on("click", ".GetForumPosts", function ()
    {
        if("userID" in localStorage)
        {
            $( "#sign-in-to-reply" ).hide();
            $( "#commentArea" ).show();
        }
        else
        {
            $( "#commentArea" ).hide();
            $( "#sign-in-to-reply" ).show();                  
        }
        pushCurrentPage(currentPage);
        showForumPosts($(this).attr("value"), $(this).attr("id"));
    });

    refreshTopic = '';

    function submitReply(postContent, topicID)
    {
        $( "#TopHeader" ).show();
        refreshTopic = topicID;
        $.post( SERVER + "/WebServices/replyToPost.php",
        {
            content: postContent,
            postID: topicID
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                alert("Reply Submitted");
                var li, a, img, h2, p1, p2, div, span;
                $.post( SERVER + "/WebServices/getTopicReplies.php",
                {
                    id: refreshTopic
                }, function (data, status)
                {
                    var jsonObj = data;
                    var forum = '';
                    var commentArea = '';

                    if (jsonObj.code == 0)
                    {
                        var postData = jsonObj.response;
                        
                        for (var i = 0; i < jsonObj.rowCount; i++)
                        {
                            li = '<li data-icon="false">';
                            a = '<a href="#">';
                            img = '<img class="li-img" src="' + postData[i].img + '">';
                            h2 = '<h2>' + postData[i].display_name + '</h2>';
                            p1 = '<p>' + postData[i].content + '</p>';
                            p2 = '<p class="c-name">' + postData[i].postDate + '</p>';
                            div = '<div class="meter-full">';
                            span = '<span class="ui-li-count margin-right">#' + postData[i].postID + '</span></div></a></li>';
                            forum = forum + li + a + img + h2 + p1 + p2 + div + span;
                        }
                        hideModal();                

                        $('#myComment').val('');
                        $('#forum-posts').html(forum);
                        $('#forum-posts').listview('refresh');
                        
                    }
                });
                
            }
            else if (jsonObj.code == -1)
            {
                $('#forum-posts').html("DB Connectivity Failed");
            }
            else
            {
                $('#forum-posts').html("Comment not submitted");
            }
        });
    };

    $(document).on("click", "#sendReply", function ()
    {
        if($('#myComment').val() != '')
            submitReply($('#myComment').val(), $(this).attr("value"));
        else
            alert("Please Fill in something to reply!");
    });

    refreshForum = '';

    function submitTopic(topicTitle, topicContent, forumId)
    {
        $( "#TopHeader" ).show();
        refreshForum = forumId;
        $.post( SERVER + "/WebServices/createTopic.php",
        {
            title : topicTitle,
            content: topicContent,
            forumID: forumId
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                alert("Topic Submitted");
                var tr, td1, td2, td3, td4;
                $.post( SERVER + "/WebServices/getForumTopics.php",
                {
                    id: refreshForum
                }, function (data, status)
                {
                    var jsonObj = data;
                    var forum = '';
                    if (jsonObj.code == 0)
                    {
                        hideModal();
                        var forumData = jsonObj.response;
                        for (var i = 0; i < jsonObj.rowCount; i++)
                        {
                            tr = '<li data-icon="false">';
                            td1 = '<a class="GetForumPosts" id="' + forumData[i].title + '" value="' + forumData[i].postID + '" href="#fourm-storie">';
                            td2 = '<h2>' + forumData[i].title + '</h2>';
                            td3 = '<p class="c-name">' + forumData[i].last_active_time + '</p>';
                            td4 = '<span class="ui-li-count">' + forumData[i].comment_count + ' Post</span></a></li>';
                                    
                            forum = forum + tr + td1 + td2 + td3 + td4;
                        }
                        
                        $("#getForumTopics").table("refresh");

                        $('#topic-title').val('');
                        $('#topicContent').val('');

                        $('#topics').html(forum);
                        $('#topics').listview('refresh');
                        
                    }
                });
            }
            else if (jsonObj.code == -1)
            {
                $('#topics').html("DB Connectivity Failed");
            }
            else
            {
                $('#topics').html("Topic not Created");
            }
        });
    };

    $(document).on("click", "#createTopic", function ()
    {
        if($('#topic-title').val() != '' && $('#topicContent').val() != '')
            submitTopic($('#topic-title').val(), $('#topicContent').val(), $(this).attr("value"));
        else
            alert("Please Fill in all the fields!");
    });

    var uID = '';
    var uPass = '';

    function signIn(uName, passwd)
    {
        
        //showModal();
        uID = uName;
        uPass = passwd;
        $.post( SERVER + "/WebServices/Login.php",
        {
            username: uName,
            password: passwd
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                //hideModal();

                $.post( SERVER + "/WebServices/checkSurvey.php",
                {
                    
                }, function (data, status)
                {
                    
                    var jsonObj = data;   

                    if (jsonObj.code == 0)
                    {
                        var jsonObj = data;            
                        if (jsonObj.code == 0)
                        {
                            var surveyData = jsonObj.response;
                            for (var i = 0; i < jsonObj.rowCount; i++)
                            {
                                for(var j = 1 ; j <= 10 ; j++)
                                {
                                    //alert('#a'+ parseInt(j) + surveyData[i].id);
                                    if($('#a'+ parseInt(j) + surveyData[i].id).val() == surveyData[i].value )
                                    {
                                        //alert(surveyData[i].value);
                                        $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true );
                                    }
                                }
                            }                                                          
                        }
                        alert("Please complete the survey by selecting [Button with 3 lines] and then selecting \"Survey\" Thank you!");
                    }
                    else if(jsonObj.code == 1)
                    {
                        alert("Please fill in the survey from the Left panel!\nThank You");
                    }

                    if (jsonObj.code == 2)
                    {
                        SurveyComplete = true;
                        $('#surveyInPanel').html('');
                        $('#surveyInPanel').listview('refresh');            
                    } 
                    else
                    {
                        $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r gamePanel">Survey</a>');
                        $('#surveyInPanel').listview('refresh'); 
                    }


                     
                });
                loggedIn = 0;
                var profileData = jsonObj.response;
                headerProfile = '<a href="#profile"class="ui-btn user-pic-name">';
                imageProfile = '<img id="profilePanelPhoto" src="'+ profileData[0].img +'" alt="" class="ui-li-icon">'+ profileData[0].display_name +'</a>';
                localStorage.setItem("userID", uID);                
                localStorage.setItem("userPassword", uPass);
                localStorage.setItem("ID", profileData[0].ID);
                alert("You have been Signed in as " + profileData[0].display_name);
                
                if(currentPage == '#sign-reg')
                {
                    pageStack.pop();
                    if($('#moviesORgames').attr("href" ) == "#home")
                    {
                        currentPage = '#movies-top-10';
                    }
                    else
                    {
                        currentPage = "#home"; 
                    }
                    
                    pushCurrentPage(currentPage,false);
                }
                
                switchPage(currentPage, false);
                pageStack.pop();

                
                
                $('#profileLink').html( headerProfile + imageProfile);
                $('#loginlogout').html( '<a id="logout" href="#home" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Sign Out</a>' );
                $('#profileLink').listview('refresh');
                $('#loginlogout').listview('refresh');
                 
                     
            }
            else if (jsonObj.code == -1)
            {
                //hideModal();
                $('#loginErrors').html("* DB Connectivity Failed");
            }
            else if(jsonObj.code == 2)
            {
                hideModal();
                $('#loginErrors').html("* Your email is not verified.<br> Please check your inbox for activation link.");
            }
            else
            {
                //hideModal();
                $('#loginErrors').html("* Invalid Username/Password");
            }
        });

    };
    $(document).on("click", "#signMeIn", function ()
    {

        signIn($('#uName').val(), $('#pass').val());
    });

    $(document).on("click", "#loginlogout", function ()
    {
        $( "#TopHeader" ).show();
        $.post( SERVER + "/WebServices/Logout.php",
        {
            
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                hideModal();
                loggedIn = 1;
                alert("You have been Signed Out, Successfully");

                localStorage.removeItem("userID");
                localStorage.removeItem("userPassword");
                localStorage.removeItem("ID");
                SurveyComplete = false;
                var profileData = jsonObj.response;
                headerProfile = ' ';
                imageProfile = ' ';
                firstTime = true;
                refreshHome();
                
                $('#profileLink').html( headerProfile + imageProfile);
                $('#loginlogout').html( '<a href="#sign-reg" class="ui-btn ui-btn-icon-right ui-icon-carat-r">SignIn / Register</a>' );
                $('#surveyInPanel').html('');
                $('#profileLink').listview('refresh');
                $('#loginlogout').listview('refresh');
                $('#surveyInPanel').listview('refresh');
                
                if($('#moviesORgames').attr("href" ) == "#movies-top-10")
                {
                    switchPage('#home', false);
                }
                else
                {
                    switchPage('#movies-top-10', false);   
                }
                
            }
            else if (jsonObj.code == -1)
            {
                hideModal();
                $('#loginErrors').html("* DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#loginErrors').html("* Invalid Username/Password");
            }
        });
    });



    $(document).on("click", "#LoginPage1", function ()
    {
        currentPage = "#sign-reg";
        pushCurrentPage(currentPage);
        $('#loginErrors').html('');
        $('#signInn')[0].reset();
        $( "#TopHeader" ).hide();
        //$('#signInn').trigger("reset");
    });

    $(document).on("click", "#LoginPage2", function ()
    {
        currentPage = "#sign-reg";
        pushCurrentPage(currentPage);
        $('#regErrors').html('');
        $('#registerationForm')[0].reset();
        $( "#TopHeader" ).hide();
        //$('#registerationForm').trigger("reset");
    });

    $(document).on("click", "#login", function ()
    {
        $( "#TopHeader" ).hide();
    });

    $(document).on("click", "#logup", function ()
    {
        $( "#TopHeader" ).hide();
    });


    // $(document).on("click", "#loginRegister", function ()
    // {
    //     pushCurrentPage(currentPage);
    // });
 
    var num1 = Math.floor((Math.random() * 5) + 1);
    var num2 = Math.floor((Math.random() * 5) + 1);
    var sum = num1 + num2;
    $('#num1').html(num1);
    $('#num2').html(num2);

    registrationData = new Array();

    function register(uName, passwd, rpasswd, e_mail, sex, dateOfBirth, captcha)
    {

        showModal();
        $('#unLabel').html('Username');
        $('#pLabel').html('Password');
        $('#prLabel').html('Re-type Password');
        $('#eLabel').html('Email');
        $('#dLabel').html('Date of Birth');
        
        var error = '<span id="errorStaric"> *</span>';
        var isValid = true;
        var error2 = '';
        if (uName == '')
        {
            //error = error + '<p>* Enter a Username</p>';
            $('#unLabel').html($('#unLabel').text() + error);
            isValid = false;
        }
        if (dateOfBirth == '')
        {
            $('#dLabel').html($('#dLabel').text() + error);
            isValid = false;
            //error = error + '<p>* Enter your DOB</p>'
        }
        if (passwd == '' )
        {
            $('#pLabel').html($('#pLabel').text() + error);
            isValid = false;
            //error = error + '<p>* Enter a Correct Password</p>';
        }
        if (rpasswd == '')
        {
            $('#prLabel').html($('#prLabel').text() + error);
            isValid = false;
            //error = error + '<p>* Enter a Correct Password</p>';
        }
        if (passwd != rpasswd)
        {
            //$('#unLabel').html($('#Label').text() + error);
            //error = error + '<p>* Passwords didn\'t Match</p>';
            error2 =  '<p>* Passwords didn\'t Match</p>';
            isValid = false;
        }

        if (!validateEmail(e_mail))
        {
            $('#eLabel').html($('#eLabel').text() + error);
            isValid = false;
            //error = error + '<p>* Enter an Email Address</p>';
        }
        if (captcha != sum)
        {
            $('#cLabel').html($('#cLabel').text() + error);
            isValid = false;
            error2 = error2 + '<p>* Captcha Test Failed</p>';
        }
        if (isValid == true)
        {
            $.post( SERVER + "/WebServices/Register.php",
            {
                username: uName,
                password: passwd,
                email: e_mail,
                gender: sex,
                dob: dateOfBirth
            }, function (data, status)
            {
                var jsonObj = data;
                if (jsonObj.code == 0)
                {
                    hideModal();
                    var regData = jsonObj.response;
                    registrationData[0] = regData[0].username;
                    registrationData[1] = regData[0].active_key;
                    registrationData[2] = regData[0].email;
                    switchPage('#thankyou', false);
                }
                else if (jsonObj == -1)
                {
                    hideModal();
                    $('#regErrors').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#regErrors').html(data.message);
                }
            });
        }
        else
        {
            hideModal();
            num1 = Math.floor((Math.random() * 5) + 1);
            num2 = Math.floor((Math.random() * 5) + 1);
            sum = num1 + num2;
            $('#cLabel').html('<span id="num1"> ' + num1 +' </span> + <span id="num2"> ' + num2 +' </span> = ? ');
            $('#captcha').val('');
            $('#regErrors').html(error2);
        }
    };
    $(document).on("click", "#signMeUp", function ()
    {
        
        register($('#u-name').val(), $('#password').val(), $('#r-password').val(), $('#email').val(), $('input[name=gender]:checked', '#registerationForm').attr("value"), $('#date').val(), $('#captcha').val());
    });

    $(document).on("click", "#resendEmail", function ()
    {
        $( "#TopHeader" ).show();
        showModal();
        $.post( SERVER + "/WebServices/resendMail.php",
        {
            username : registrationData[0],
            activation_key : registrationData[1],
            email : registrationData[2]
            
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                hideModal();
                
                alert("Email has been Resent Successfully");                   
            }
            else if (jsonObj.code == -1)
            {
                hideModal();
                
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                
                alert("Invalid Username/Password");
            }
        });
    });

    function validateEmail(email) 
    { 
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }; 


    var gameProperties = new Array();
    gameProperties[0] = {lable:"Graphics", value:0, id:"Graphics"};
    gameProperties[1] = {lable:"Sound Effects", value:0, id:"Sound_Effects"};
    gameProperties[2] = {lable:"Music", value:0, id:"Music"};
    gameProperties[3] = {lable:"Control", value:0, id:"Control"};
    gameProperties[4] = {lable:"Difficulty", value:0, id:"Difficulty"};
    gameProperties[5] = {lable:"Gameplay", value:0, id:"Gameplay"};
    gameProperties[6] = {lable:"Replay Value", value:0, id:"Replay_Value"};

    var genresProperties = new Array();    
    // genresProperties[0] = {label:"2D Fighter", point:0, id:26};
    // genresProperties[1] = {label:"3D Fighter", point:0, id:28};
    // genresProperties[2] = {label:"Action", point:0, id:29};
    // genresProperties[3] = {label:"Adventure", point:0, id:30};
    // genresProperties[4] = {label:"Board Game", point:0, id:117};
    
    // genresProperties[5] = {label:"Casual", point:0, id:109};
    // genresProperties[6] = {label:"Children's", point:0, id:128};
    // genresProperties[7] = {label:"Crafting", point:0, id:122};
    // genresProperties[8] = {label:"Creation - Characters", point:0, id:111};
    // genresProperties[9] = {label:"Creation - Worlds", point:0, id:110};
    
    // genresProperties[10] = {label:"Cross Overs", point:0, id:113};
    // genresProperties[11] = {label:"Driving - Arcade", point:0, id:31};
    // genresProperties[12] = {label:"Driving - Simulation", point:0, id:32};
    // genresProperties[13] = {label:"Educational", point:0, id:33};
    // genresProperties[14] = {label:"Endless Runner", point:0, id:120};
    
    // genresProperties[15] = {label:"Exploration", point:0, id:95};
    // genresProperties[16] = {label:"Family Friendly", point:0, id:34};
    // genresProperties[17] = {label:"Fantasy", point:0, id:112};
    // genresProperties[18] = {label:"First Person", point:0, id:36};
    // genresProperties[19] = {label:"First Person Shooter", point:0, id:125};
    
    // genresProperties[20] = {label:"Fitness", point:0, id:136};
    // genresProperties[21] = {label:"Flight", point:0, id:37};
    // genresProperties[22] = {label:"Free to play/Pay to Win", point:0, id:71};
    // genresProperties[23] = {label:"Grinding", point:0, id:39};
    // genresProperties[24] = {label:"Hack &amp; Slash", point:0, id:101};
    
    // genresProperties[25] = {label:"Horror", point:0, id:134};
    // genresProperties[26] = {label:"Humor", point:0, id:93};
    // genresProperties[27] = {label:"Indie", point:0, id:135};
    // genresProperties[28] = {label:"Kart Racing", point:0, id:138};
    // genresProperties[29] = {label:"Life Sim", point:0, id:106};
    
    // genresProperties[30] = {label:"Light Gun", point:0, id:102};
    // genresProperties[31] = {label:"Loot", point:0, id:92};
    // genresProperties[32] = {label:"Military", point:0, id:96};
    // genresProperties[33] = {label:"MMORPG", point:0, id:47};
    // genresProperties[34] = {label:"Multiplayer", point:0, id:45};
    
    // genresProperties[35] = {label:"Music/Rhythm", point:0, id:129};
    // genresProperties[36] = {label:"On Rails", point:0, id:104};
    // genresProperties[37] = {label:"Party", point:0, id:126};
    // genresProperties[38] = {label:"Pinball", point:0, id:40};
    // genresProperties[39] = {label:"Platformer", point:0, id:41};
    
    // genresProperties[40] = {label:"Puzzle", point:0, id:42};
    // genresProperties[41] = {label:"Quick Time Events", point:0, id:115};
    // genresProperties[42] = {label:"Retro", point:0, id:127};
    // genresProperties[43] = {label:"RPG - Japanese", point:0, id:48};
    // genresProperties[44] = {label:"RPG - Western", point:0, id:124};
    
    // genresProperties[45] = {label:"Sandbox", point:0, id:49};
    // genresProperties[46] = {label:"Sci-Fi", point:0, id:50};
    // genresProperties[47] = {label:"Sexy", point:0, id:43};
    // genresProperties[48] = {label:"Shoot em Up", point:0, id:103};
    // genresProperties[49] = {label:"Simulation", point:0, id:52};
    
    // genresProperties[50] = {label:"Sports - Baseball", point:0, id:54};
    // genresProperties[51] = {label:"Sports - Basketball", point:0, id:55};
    // genresProperties[52] = {label:"Sports - Bowling", point:0, id:137};
    // genresProperties[53] = {label:"Sports - Boxing", point:0, id:56};
    // genresProperties[54] = {label:"Sports - College", point:0, id:61};
    
    // genresProperties[55] = {label:"Sports - Extreme", point:0, id:59};
    // genresProperties[56] = {label:"Sports - Football", point:0, id:53};
    // genresProperties[57] = {label:"Sports - Golf", point:0, id:58};
    // genresProperties[58] = {label:"Sports - Hockey", point:0, id:133};
    // genresProperties[59] = {label:"Sports - Soccer", point:0, id:60};
    
    // genresProperties[60] = {label:"Sports - Tennis", point:0, id:57};
    // genresProperties[61] = {label:"Squad", point:0, id:62};
    // genresProperties[62] = {label:"Stealth", point:0, id:105};
    // genresProperties[63] = {label:"Story", point:0, id:65};
    // genresProperties[64] = {label:"Strategy - Real Time", point:0, id:63};
    
    // genresProperties[65] = {label:"Strategy - Turn Based", point:0, id:64};
    // genresProperties[66] = {label:"Super Heroes", point:0, id:114};
    // genresProperties[67] = {label:"Survival Horror", point:0, id:66};
    // genresProperties[68] = {label:"Thinking", point:0, id:118};
    // genresProperties[69] = {label:"Third Person", point:0, id:68};
    
    // genresProperties[70] = {label:"Third Person Brawler", point:0, id:67};
    // genresProperties[71] = {label:"Touch Controls", point:0, id:69};
    // genresProperties[72] = {label:"Tower Defense", point:0, id:91};
    // genresProperties[73] = {label:"Trivia", point:0, id:108};
    // genresProperties[74] = {label:"Violence", point:0, id:44};
    
    // genresProperties[75] = {label:"Wrestling", point:0, id:70};
    // genresProperties[76] = {label:"Writing", point:0, id:140};

    function printSurvey()
    {

        $.post( SERVER + "/WebServices/getSurveyQuestions.php",
        {
            
        }, function (data, status)
        {

            var jsonObj = data;
                        
            if (jsonObj.code == 0)
            {
                  genresProperties = jsonObj.response;  

                var p1 , p2 , p3 , p4 , p5 , p6 , p7 , p8 , p9 , p10, p11, p12 , p13, p14, p15, p16, p17 , p18 , p19 , p20 , p21, p22 , p23 ;
                var surForm = '';
                var i = 0;
                for( i = 0 ; i < 7 ; i++)
                {

                    p1 = '<fieldset data-role="controlgroup" data-type="horizontal"data-theme="b">';
                    p2 = '<legend>' + gameProperties[i]["lable"] +'</legend>';
                    p3 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a1' + gameProperties[i]["id"] + '" value="1">';
                    p4 = '<label for="a1' + gameProperties[i]["id"] + '">1</label>';
                    p5 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a2' + gameProperties[i]["id"] + '" value="2">';
                    p6 = '<label for="a2' + gameProperties[i]["id"] + '">2</label>';
                    p7 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a3' + gameProperties[i]["id"] + '" value="3">';
                    p8 = '<label for="a3' + gameProperties[i]["id"] + '">3</label>';
                    p9 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a4' + gameProperties[i]["id"] + '" value="4">';
                    p10 = '<label for="a4' + gameProperties[i]["id"] + '">4</label>';
                    p11 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a5' + gameProperties[i]["id"] + '" value="5">';
                    p12 = '<label for="a5' + gameProperties[i]["id"] + '">5</label>';
                    p13 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a6' + gameProperties[i]["id"] + '" value="6">';
                    p14 = '<label for="a6' + gameProperties[i]["id"] + '">6</label>';
                    p15 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a7' + gameProperties[i]["id"] + '" value="7">';
                    p16 = '<label for="a7' + gameProperties[i]["id"] + '">7</label>';
                    p17 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a8' + gameProperties[i]["id"] + '" value="8">';
                    p18 = '<label for="a8' + gameProperties[i]["id"] + '">8</label>';
                    p19 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a9' + gameProperties[i]["id"] + '" value="9">';
                    p20 = '<label for="a9' + gameProperties[i]["id"] + '">9</label>';
                    p21 = '<input type="radio" name="' + gameProperties[i]["id"] + '" id="a10' + gameProperties[i]["id"] + '" value="10">';
                    p22 = '<label for="a10' + gameProperties[i]["id"] + '">10</label>';
                    p23 = '</fieldset>';
                    

                    surForm = surForm + p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p14 + p15 + p16 + p17 + p18 + p19 + p20 + p21+ p22 + p23 ;

                }

                var buttons =   '<p style="text-align: center;"><a id="saveDraft" href="#" class="red ui-btn ui-btn-inline pad-btn saveDraft">Save</a><a value="#GenreSurvey0" class="red ui-btn ui-btn-inline pad-btn NextPage">Next</a></p>';

                $('#surveyForm').html(surForm + buttons);
                $('#surveyForm').trigger('create');

                var GenForm = '';
                for( i = 0 ; i < 77 ; i++)
                {
                    p1 = '<fieldset data-role="controlgroup" data-type="horizontal"data-theme="b">';
                    p2 = '<legend>' + genresProperties[i]["label"] +'</legend>';
                    p3 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a1' + genresProperties[i]["id"] + '" value="1">';
                    p4 = '<label for="a1' + genresProperties[i]["id"] + '">1</label>';
                    p5 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a2' + genresProperties[i]["id"] + '" value="2">';
                    p6 = '<label for="a2' + genresProperties[i]["id"] + '">2</label>';
                    p7 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a3' + genresProperties[i]["id"] + '" value="3">';
                    p8 = '<label for="a3' + genresProperties[i]["id"] + '">3</label>';
                    p9 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a4' + genresProperties[i]["id"] + '" value="4">';
                    p10 = '<label for="a4' + genresProperties[i]["id"] + '">4</label>';
                    p11 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a5' + genresProperties[i]["id"] + '" value="5">';
                    p12 = '<label for="a5' + genresProperties[i]["id"] + '">5</label>';
                    p13 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a6' + genresProperties[i]["id"] + '" value="6">';
                    p14 = '<label for="a6' + genresProperties[i]["id"] + '">6</label>';
                    p15 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a7' + genresProperties[i]["id"] + '" value="7">';
                    p16 = '<label for="a7' + genresProperties[i]["id"] + '">7</label>';
                    p17 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a8' + genresProperties[i]["id"] + '" value="8">';
                    p18 = '<label for="a8' + genresProperties[i]["id"] + '">8</label>';
                    p19 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a9' + genresProperties[i]["id"] + '" value="9">';
                    p20 = '<label for="a9' + genresProperties[i]["id"] + '">9</label>';
                    p21 = '<input type="radio" name="' + genresProperties[i]["id"] + '" id="a10' + genresProperties[i]["id"] + '" value="10">';
                    p22 = '<label for="a10' + genresProperties[i]["id"] + '">10</label>';
                    p23 = '</fieldset>';

                    GenForm = GenForm + p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9 + p10 + p11 + p12 + p13 + p14 + p15 + p16 + p17 + p18 + p19 + p20 + p21+ p22 + p23 ;

                    if((i+1)%10 == 0)
                    {
                        if(i <= 10)
                            buttons =   '<p style="text-align: center;"><a href="#survey" class="red ui-btn ui-btn-inline pad-btn">Prev</a><a id="saveDraft" href="#" class="red ui-btn ui-btn-inline pad-btn saveDraft">Save</a><a value="#GenreSurvey' + parseInt((i)/10 + 1) + '" class="red ui-btn ui-btn-inline pad-btn NextPage">Next</a></p>';
                        else
                            buttons =   '<p style="text-align: center;"><a href="#GenreSurvey' + parseInt((i)/10 -1 ) + '" class="red ui-btn ui-btn-inline pad-btn">Prev</a><a id="saveDraft" href="#" class="red ui-btn ui-btn-inline pad-btn saveDraft">Save</a><a value="#GenreSurvey' + parseInt((i)/10 + 1) + '" class="red ui-btn ui-btn-inline pad-btn NextPage">Next</a></p>';
                        
                        $('#surveyForm'+ parseInt((i+1)/10 - 1) ).html(GenForm + buttons);
                        $('#surveyForm'+ parseInt((i+1)/10 - 1) ).trigger('create');
                        GenForm = '';
                    }

                    
                }

                
                buttons =   '<p style="text-align: center;"><a href="#GenreSurvey6" class="red ui-btn ui-btn-inline pad-btn">Prev</a><a id="submitSurvey" href="#" class="red ui-btn ui-btn-inline pad-btn">Submit</a></p>';

                $('#surveyForm7').html(GenForm + buttons);
                $('#surveyForm7').trigger('create');
                checkSurveyOptions();
                // setTimeout( function()
                // {
                //     checkSurveyOptions();
                // }, 3000 );           
            }
            else if (jsonObj.code == -1)
            {
                alert("DB Connectivity Failed");
            }
            else
            {
                alert("Could not fetch Survey Questions!");
            }
        });  
                
    };

    $(document).on("click", "#doSurvey", function ()
    {
        $('#surveyForm').html('Survey is being Loaded. Please wait...');
        $( "#TopHeader" ).hide();
        if(currentPage != '#survey')
            pushCurrentPage(currentPage);
        currentPage = '#survey';
        printSurvey();        
    });

    $(document).on("click", ".NextPage", function ()
    {
        var status = 1;
        if($(this).attr("value" ) == '#GenreSurvey0' )
        {
            for( i = 0 ; i < 7 ; i++)
            {
                if($('input[name='+ gameProperties[i]["id"] + ']:checked', '#surveyForm').attr("value") == undefined)
                {
                    status = 0;
                    break;
                }
            }
        }
        else 
        {
            var start = 0;
            if( $(this).attr("value" ) == '#GenreSurvey1' )
            {
                start = 0;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey2' )
            {
                start = 10;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey3' )
            {
                start = 20;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey4' )
            {
                start = 30;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey5' )
            {
                start = 40;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey6' )
            {
                start = 50;
            }
            else  if( $(this).attr("value" ) == '#GenreSurvey7' )
            {
                start = 60;
            }
            for( i = start ; i < (start + 10) && i < 77 ; i++)
            {
                if($('input[name='+ genresProperties[i]["id"] + ']:checked', '#surveyForm'+ parseInt((i)/10)).attr("value") == undefined)
                {
                    status = 0;
                    break;
                }
            }
        }
        if(status == 1)
        {
            switchPage($(this).attr("value" ) , false);   
        }else
             alert("Please Fill in all the Question to proceed!");
    });

    function saveDraft()
    {
        //$( "#TopHeader" ).show();
        var gameDraft = [];
        var genreDraft = [];
        var j = 0;
        var tempArray = [];
        var tempVar = '';
        var tempVar1 = '';
        var i;
        for( i = 0 ; i < 7 ; i++)
        {
            if($('input[name='+ gameProperties[i]["id"] + ']:checked', '#surveyForm').attr("value") != undefined)
            {
                tempVar = gameProperties[i]["id"];
                tempVar1 = gameProperties[i]["lable"];
                tempArray = {lable:tempVar1, value:($('input[name='+ gameProperties[i]["id"] + ']:checked', '#surveyForm').attr("value")), id:tempVar};
                gameDraft.push(tempArray);
            }
        }

        for( i = 0 ; i < 77 ; i++)
        {
            if($('input[name='+ genresProperties[i]["id"] + ']:checked', '#surveyForm'+ parseInt((i)/10)).attr("value") != undefined)
            {
                tempVar = genresProperties[i]["id"];
                tempVar1 = genresProperties[i]["label"];
                tempArray = {label:tempVar1, point:($('input[name='+ genresProperties[i]["id"] + ']:checked', '#surveyForm'+ parseInt((i)/10 )).attr("value")), id:tempVar};
                genreDraft.push(tempArray);
            }
        }
        
        // var gameData = JSON.stringify(gameDraft);
        // var genreData = JSON.stringify(genreDraft);  
        // alert(genreDraft);     

        $.post( SERVER + "/WebServices/saveSurveyDraft.php",
        {
            game: gameDraft,
            genre: genreDraft
        }, function (data, status)
        {

            var jsonObj = data;
                        
            if (jsonObj.code == 0)
            {
                alert("Survey Saved Successfully");
                currentPage = '#home';
                switchPage(currentPage, false);                   
            }
            else if (jsonObj.code == -1)
            {
                alert("DB Connectivity Failed");
            }
            else
            {
                alert("Survey could not be saved at the moment");
            }
        });
    };

    $(document).on("click", "#saveDraft", function ()
    {
        saveDraft();
        
    });

    function submitSurvey()
    {
        //$( "#TopHeader" ).show();
        var incompleteSurvey = false;
        var gameDraft = [];
        var genreDraft = [];
        var j = 0;
        var tempArray = [];
        var tempVar = '';
        var tempVar1 = '';
        var i;
        for( i = 0 ; i < 7 && !incompleteSurvey ; i++)
        {
            if($('input[name='+ gameProperties[i]["id"] + ']:checked', '#surveyForm').attr("value") != undefined)
            {
                tempVar = gameProperties[i]["id"];
                tempVar1 = gameProperties[i]["lable"];
                tempArray = {lable:tempVar1, value:($('input[name='+ gameProperties[i]["id"] + ']:checked', '#surveyForm').attr("value")), id:tempVar};
                gameDraft.push(tempArray);
            }
            else
            {
                incompleteSurvey = true;
            }
        }

        for( i = 0 ; i < 77 && !incompleteSurvey ; i++)
        {
            if($('input[name='+ genresProperties[i]["id"] + ']:checked', '#surveyForm'+ parseInt((i)/10)).attr("value") != undefined)
            {
                tempVar = genresProperties[i]["id"];
                tempVar1 = genresProperties[i]["label"];
                tempArray = {label:tempVar1, point:($('input[name='+ genresProperties[i]["id"] + ']:checked', '#surveyForm'+ parseInt((i)/10 )).attr("value")), id:tempVar};
                genreDraft.push(tempArray);
            }
            else
            {
                incompleteSurvey = true;
            }
        }

        if(incompleteSurvey)
        {
            alert("Please fill in the complete survey to submit");
        }
        else
        {  
            $.post( SERVER + "/WebServices/submitSurvey.php",
            {
                game: gameDraft,
                genre: genreDraft
            }, function (data, status)
            {

                var jsonObj = data;
                

                
                if (jsonObj.code == 0)
                {
                    alert("Survey Submitted!\nResults being calculated by the SprezzEngine. You personalized scores will display shortly.");
                    SurveyComplete = true;
                     
                    currentPage = '#home';
                    switchPage(currentPage, false);   
                    $('#surveyInPanel').html('');
                    $('#surveyInPanel').listview('refresh');
                }
                else if (jsonObj.code == -1)
                {
                    alert("DB Connectivity Failed");
                }
                else
                {
                    alert("Survey could not be submitted at the moment");
                }
            });
        }
    };

    $(document).on("click", "#submitSurvey", function ()
    {
        submitSurvey();     
    });


    function showYourTop250Games()
    {
        $( "#TopHeader" ).show();
        currentPage = '#top-250';
        $('#paginate250').html('');
        showModal();
        var ID = localStorage.getItem("ID");
        $('#CalculatedGamesList').html(' ');
        $('#Top250GamesHeading').html('Your Top 250 Games');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getUserTopGames.php",
        {
            userID : ID,
            limit: 250
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount && i <= 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                var pagination = '';
                pagination = '<a value="0" class="page red ui-btn yp">« First</a>';
                var i = 1;
                for( i = 1 ; i < (jsonObj.rowCount/25)-1 ; i++)
                {
                    pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn yp">'+parseInt(i+1)+'</a>';                    
                }

                pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn yp">Last »</a>';

                hideModal();
                $('#CalculatedGamesList').html(game);
                $('#CalculatedGamesList').listview('refresh');

                $('#paginate250').html(pagination);
                
                

            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#CalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                if(!("userID" in localStorage) )
                    $('#CalculatedGamesList').html('<p>Please <a id="login" href="#sign-in">Sign in</a> or <a id="logup" href="#register">Register</a>  and then fill in the survey to view Your Top 250 Games.</p>');    
                else if(SurveyComplete)
                    $('#CalculatedGamesList').html('<p>Please come back after some time. Your Survey is being calculated. Thank You!</p>');    
                else
                    $('#CalculatedGamesList').html('<p>Please complete <a id="doSurvey" href="#survey">Survey</a> to be able to view your top 250 Games.</p>');
                
            }
        });
    };

    $(document).on("click", "#top250Games", function ()
    {
        if(currentPage != '#top-250')
            pushCurrentPage(currentPage);
        showYourTop250Games();     
    }); 

    var limit = 0;

    function showNextinYour250()
    {
        $( "#TopHeader" ).show();
        $('#CalculatedGamesList').html(' ');
        showModal();
        var ID = localStorage.getItem("ID");
        $.post( SERVER + "/WebServices/getUserTopGames.php",
        {
            userID: ID,
            limit: 250
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = limit + 1; i <= jsonObj.rowCount && i <= limit + 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                
                hideModal();
                $('#CalculatedGamesList').html(game);
                $('#CalculatedGamesList').listview('refresh');              
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#CalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#CalculatedGamesList').html("No Game Found");
                $('#paginate250').html("");
            }
        });

    };

    

    $(document).on("click", ".yp", function ()
    {
        limit = parseInt($(this).attr("value"));
        showNextinYour250(); 
    }); 






    function showOurTop250Games()
    {
        $( "#TopHeader" ).show();
        currentPage = '#our-top-250';
        $('#ourpaginate250').html('');
        showModal();

        $('#OurCalculatedGamesList').html(' ');
        $('#Top250GamesHeading').html('Our Top 250 Games');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getTop250Games.php",
        {

        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                var pagination = '';
                for (var i = 1; i <= jsonObj.rowCount && i <= 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                
                pagination = '<a value="0" class="page red ui-btn op">« First</a>';
                var i = 1;
                for( i = 1 ;  i < (jsonObj.rowCount/25)-1 ; i++)
                {
                    pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn op">'+parseInt(i+1)+'</a>';                    
                }

                pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn op">Last »</a>';

                hideModal();
                $('#OurCalculatedGamesList').html(game);
                $('#OurCalculatedGamesList').listview('refresh');

                $('#ourpaginate250').html(pagination);
                
                

            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#OurCalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#OurCalculatedGamesList').html("No Game Found!");
            }
        });
    };

    $(document).on("click", "#ourtop250Games", function ()
    {
        if(currentPage != '#top-250')
            pushCurrentPage(currentPage);
        showOurTop250Games();    
    }); 

    var limit = 0;

    function showNextinOur250()
    {
        $( "#TopHeader" ).show();
        $('#OurCalculatedGamesList').html(' ');
        showModal();
        $.post( SERVER + "/WebServices/getTop250Games.php",
        {
            
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = limit + 1; i <= jsonObj.rowCount && i <= limit + 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                
                hideModal();
                $('#OurCalculatedGamesList').html(game);
                $('#OurCalculatedGamesList').listview('refresh');              
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#OurCalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#OurCalculatedGamesList').html("No Game Found");
                $('#paginate250').html("");
            }
        });

    };

    $(document).on("click", ".op", function ()
    {
        limit = parseInt($(this).attr("value"));
        showNextinOur250(); 
    });

    var answers;
    var pID;
    $(document).on("click", "#poll", function ()
    {
        $( "#TopHeader" ).show();
        showModal();
        $.post( SERVER + "/WebServices/getDailyPoll.php",
        {
            
        }, function (data, status)
        {
            var jsonObj = data;
            
            if (jsonObj.code == 0)
            {
                hideModal(); 
                var pollData = jsonObj.response;
                poll = '';
                answers = pollData[0].answers;
                pID = pollData[0].postID;
                for (var i = 0 ; i < pollData[0].answers.length ; i++)
                {
                    label =    '<label for="'+ parseInt(pollData[0].answers[i].id) +'">'+ pollData[0].answers[i].answer +'</label>';
                    input =    '<input type="radio"id="'+ parseInt(pollData[0].answers[i].id) +'" value="'+ parseInt(pollData[0].answers[i].counter) +'" name="ans">';    
                    poll = poll + label + input;
                } 
                $('#pollQuestion').html(pollData[0].question);
                $('#pollOptions').html(poll);
                $('#pollOptions').trigger('create');

            }
           else if (jsonObj == -1)
            {
                hideModal();
                $('#dailypoll').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#dailypoll').html("Error Fetching Polls, Try Again!");
            }
        });
    });

    $(document).on("click", "#pollResults", function ()
    {
        if($("input[name=ans]").is(":checked"))
        {
            var answerID = $('input[type=radio][name=ans]:checked').attr('id');
            for( var i = 0 ; i < answers.length ; i++)
            {
                if(answers[i].id == answerID)
                {
                    answers[i].counter = parseInt(answers[i].counter) + 1;
                    break;
                }
            }

            showModal();

            $.post( SERVER + "/WebServices/updatePollResults.php",
            {
                pollID : pID,
                answer : answers
            }, function (data, status)
            {
                var jsonObj = data;
                if (jsonObj.code == 0)
                {
                    hideModal(); 
                    poll = '';
                    sum = 0;
                    for (var i = 0 ; i < answers.length ; i++)
                    {
                        sum = sum + parseInt(answers[i].counter);
                    } 
                    var yourAnswer = $('input[type=radio][name=ans]:checked').attr('id');
                    for (var i = 0 ; i < answers.length ; i++)
                    {
                        label =    '<label for="'+ parseInt(answers[i].id) +'">'+ answers[i].answer +'<span style="float:right"> '+ ((parseFloat(answers[i].counter)/sum)*100).toFixed(1) +' %</span></label>';
                        if(answers[i].id == yourAnswer)
                            input =    '<input type="radio"id="'+ parseInt(answers[i].id) +'" value="'+ parseInt(answers[i].counter) +'" name="ans" checked> ';    
                        else
                            input =    '<input type="radio"id="'+ parseInt(answers[i].id) +'" value="'+ parseInt(answers[i].counter) +'" name="ans"> ';    
                        poll = poll + label + input;
                    } 
                    
                    $('#pollOptions').html(poll);
                    $('#pollOptions').trigger('create');
                    $("#pollResults").prop("disabled",true);

                }
               else if (jsonObj == -1)
                {
                    hideModal();
                    $('#dailypoll').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#dailypoll').html("Error Submitting Polls, Try Again!");
                }
            });
        }
        else
        {
            alert("Please choose some option first!");
        }
        
    });

    function gamesHomePage()
    {
        
        $( "#TopHeader" ).show();
        if("userID" in localStorage)
        {
            currentPage = "#home";
            //pushCurrentPage(currentPage);
            //showModal();
            var ID = localStorage.getItem("ID");
            $('#mainTop10').html(' ');
            $('#TopGamesHeadingHome').html('Your Hot 10 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getUserTopGames.php",
            {
                userID: ID,
                limit: 10
            }, function (data, status)
            {
                var jsonObj = data;
                var game = '';

                if (jsonObj.code == 0)
                {        
                    //printSurvey();    
                    $.post( SERVER + "/WebServices/checkSurvey.php",
                    {
                        
                    }, function (data, status)
                    {

                        var jsonObj = data;            
                        if (jsonObj.code == 0)
                        {
                            
                            var surveyData = jsonObj.response;
                            for (var i = 0; i < jsonObj.rowCount; i++)
                            {
                                for(var j = 1 ; j <= 10 ; j++)
                                {
                                    //alert('#a'+ parseInt(j) + surveyData[i].id);
                                    if($('#a'+ parseInt(j) + surveyData[i].id).val() == surveyData[i].value )
                                    {
                                        //alert(surveyData[i].value);
                                        $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true );
                                    }
                                }

                            }                                                          
                        }

                        
                    });

                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        //div_img = '';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    //hideModal();
                    $( ".gamePanel" ).show();
                    $( ".moviePanel" ).hide();
                    $( ".gameHeader" ).show();
                    $( ".movieHeader" ).hide();

                    $('#mainTop10').html(game);
                    $('#moviesORgames').html("Movies");
                    $("#moviesORgames").attr( "href", "#movies-top-10" );
                    $('#mainTop10').listview('refresh');
                    $('#mainTop10').listview('refresh');

                    // $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Survey</a>');
                    // $('#surveyInPanel').listview('refresh');
                   
                    
                }
                else if (jsonObj == -1)
                {
                    //hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    //hideModal();
                    refreshHome();
                }
            });
        }
        else
        {
            currentPage = "#home";
            //pushCurrentPage(currentPage);
            showModal();
            $('#mainTop10').html(' ');
            $('#TopGamesHeadingHome').html('Top 10 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getTopTenGames.php",
            {
                platform: 'all'
            }, function (data, status)
            {

                var jsonObj = data;
                var game = '';
                if (jsonObj.code == 0)
                {
                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        //div_img = '';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    hideModal();
                   // printSurvey();
                    $( ".gamePanel" ).show();
                    $( ".moviePanel" ).hide();
                    $( ".gameHeader" ).show();
                    $( ".movieHeader" ).hide();

                    $('#mainTop10').html(game);
                    $('#moviesORgames').html("Movies");
                    $("#moviesORgames").attr( "href", "#movies-top-10" );
                    $('#mainTop10').listview('refresh');
                    $('#surveyInPanel').html('');
                    $('#surveyInPanel').listview('refresh');


                    $('#moviesORgames').listview('refresh');
 
                }
                else if (jsonObj == -1)
                {
                    hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#mainTop10').html("No Game Found");
                }
            });
        }
    };

    function moviesHomePage()
    {
        
        $( "#TopHeader" ).show();
    /*    if("userID" in localStorage)
        {
            currentPage = "#home";
            
            //showModal();
            $('#mainTop10').html(' ');
            var ID = localStorage.getItem("ID");
            $('#TopGamesHeadingHome').html('Your Top 10 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getUserTopGames.php",
            {
                userID: ID,
                limit: 10
            }, function (data, status)
            {
                var jsonObj = data;
                var game = '';

                if (jsonObj.code == 0)
                {        
                    //printSurvey();    
                    $.post( SERVER + "/WebServices/checkSurvey.php",
                    {
                        
                    }, function (data, status)
                    {

                        var jsonObj = data;            
                        if (jsonObj.code == 0)
                        {
                            
                            var surveyData = jsonObj.response;
                            for (var i = 0; i < jsonObj.rowCount; i++)
                            {
                                for(var j = 1 ; j <= 10 ; j++)
                                {
                                    //alert('#a'+ parseInt(j) + surveyData[i].id);
                                    if($('#a'+ parseInt(j) + surveyData[i].id).val() == surveyData[i].value )
                                    {
                                        //alert(surveyData[i].value);
                                        $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true );
                                    }
                                }

                            }                                                          
                        }

                        
                    });

                    var gameData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                        //div_img = '';
                        img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                        game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    //hideModal();
                    $('#mainTop10').html(game);
                    $('#mainTop10').listview('refresh');

                    // $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Survey</a>');
                    // $('#surveyInPanel').listview('refresh');

                    
                }
                else if (jsonObj == -1)
                {
                    //hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    //hideModal();
                    refreshHome();
                }
            });
        }
        else

    */
        {
            currentPage = "#movies-top-10";
            
            showModal();
            $('#mainMovies').html(' ');
            $('#TopMoviesHeadingHome').html('Top 10 Movies');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getTopMoviesAndShows.php",
            {
                limit: 10
            }, function (data, status)
            {
                
                var jsonObj = data;
                var movie = '';
                if (jsonObj.code == 0)
                {
                    var movieData = jsonObj.response;
                    for (var i = 1; i <= jsonObj.rowCount; i++)
                    {
                        li = '<li data-icon="false">';
                        a = '<a class="GetMovieID" id="TopMovie' + parseInt(i) + '" href="#movie-detail" value="' + movieData[i - 1].postID + '">';
                        img = '<div class="li-img"><img src="' + movieData[i - 1].img + '">';
                        span1 = '<span class="discrip">' + i + '</span></div>';
                        h2 = '<div class="li-detail"><h2>&nbsp ' + movieData[i - 1].title + '</h2>';
                        p1 = '<p class="limitText">&nbsp ' + movieData[i - 1].content + '</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + movieData[i - 1].postDate + ' | ' + movieData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((parseInt(movieData[i - 1].rating)) / 10));
                        if (parseInt(((parseInt(movieData[i - 1].rating)) / 10)) >= 100)
                        {
                            div = '<div class="meter-full">';
                            thermometer = '<div class="thermometer"></div></div></a></li>';
                        }
                        else
                        {
                            div = '<div class="meter">';
                            thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                        }
                        span2 = '<span class="ui-li-count">' + movieData[i - 1].rating + '</span>';
                        movie = movie + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                    }
                    hideModal();
                   // printSurvey();
                    $( ".moviePanel" ).show();
                    $( ".gamePanel" ).hide();
                    $( ".movieHeader" ).show();
                    $( ".gameHeader" ).hide();

                    $('#mainMovies').html(movie);
                    $('#mainMovies').listview('refresh');
                    // $('#surveyInPanel').html('');
                    // $('#surveyInPanel').listview('refresh');

                    $('#moviesORgames').html("Games");
                    $("#moviesORgames").attr( "href", "#home" );
                    $('#moviesORgames').listview('refresh');

                    
                }
                else if (jsonObj == -1)
                {
                    hideModal();
                    $('#mainTop10').html("DB Connectivity Failed");
                }
                else
                {
                    hideModal();
                    $('#mainTop10').html("No Movie Found");
                }
            });
        }
    };

    $(document).on("click", "#moviesORgames", function ()
    {
        if($('#moviesORgames').attr("href" ) == "#movies-top-10")
        {
            pushCurrentPage(currentPage);
            moviesHomePage();

        }
        else
        {
            pushCurrentPage(currentPage);
            gamesHomePage();
            
        }
        
         
    });


    function getTopTenMovies(noOfMovies)
    {
        $( "#TopHeader" ).show();
        currentPage = "#top-10-movies";
        showModal();
        $('#top10Movies').html(' ');
        $('#TopMoviesHeading').html('Top 10 Movies');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getTopMoviesAndShows.php",
        {
            limit: noOfMovies
        }, function (data, status)
        {
            
            var jsonObj = data;
            var movie = '';
            if (jsonObj.code == 0)
            {
                var movieData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetMovieID" id="TopMovie' + parseInt(i) + '" href="#movie-detail" value="' + movieData[i - 1].postID + '">';
                    img = '<div class="li-img"><img src="' + movieData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + movieData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + movieData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + movieData[i - 1].postDate + ' | ' + movieData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(movieData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(movieData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + movieData[i - 1].rating + '</span>';
                    movie = movie + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                hideModal();
               

                $('#top10Movies').html(movie);
                $('#top10Movies').listview('refresh');
                
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#top10Movies').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#top10Movies').html("No Movie Found");
            }
        });
    };
   
    $(document).on("click", ".GetMovies", function ()
    {
        if(currentPage != '#top-10-movies')
            pushCurrentPage(currentPage);
        getTopTenMovies($(this).attr("value"));
    });

    
    var movieCatType = 'all';

    function showMovieResults(category)
    {
        $( "#TopHeader" ).show();
        showModal();
        movieCatType = category;
        $('#movieResults').html(' ');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getAllMoviesOrShows.php",
        {
            platform: category
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetMovieID" id="TopGame' + parseInt(i) + '" href="#movie-detail" value="' + gameData[i - 1].postID + '">';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '</div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                hideModal();
                $('#movieResults').html(game);
                $('#movieResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#movieResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#movieResults').html("No Movie/TV Show Found");
            }
        });
    };
    $(document).on("click", ".movieCategory", function ()
    {

        if(currentPage != '#search-movie')
        {
            currentPage = '#search-movie';
            pushCurrentPage(currentPage);
        }
        showMovieResults($(this).attr("value"));
    });

    function showMovieResultsByApha(alpha)
    {
        $( "#TopHeader" ).show();
        showModal();
        $('#movieResults').html(' ');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getAllMoviesOrShows.php",
        {
            platform: movieCatType,
            alphabet: alpha
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetMovieID" id="TopGame' + parseInt(i) + '" href="#movie-detail" value="' + gameData[i - 1].postID + '">';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '</div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                hideModal();
                $('#movieResults').html(game);
                $('#movieResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#movieResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#movieResults').html("No Game Found");
            }
        });
    };
    $(document).on("click", ".movieCatAlpha", function ()
    {
        showMovieResultsByApha($(this).text());
    });
    $(document).on("click", "#allMovies", function ()
    {
        if(currentPage != '#search-movie')
        {
            currentPage = '#search-movie';
            pushCurrentPage(currentPage);
        }
        showMovieResults('all');
    });

    function showMoviesDetails(post_id)
    {
        $( "#TopHeader" ).show();
        currentPage = "#movie-detail";
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        $('#showMovieDetails').html(' ');
        $.post( SERVER + "/WebServices/getDetailsOfGame.php",
        {
            id: post_id
        }, function (data, status)
        {
            var jsonObj = data;
            var details = '';
            if (jsonObj.code == 0)
            {
                var movieDetails = jsonObj.response;
                div1 = '<div class="head">';
                h2 = '<h2>' + movieDetails[0].title + '</h2>';
                p1 = '<p class="c-name">posted by: ' + movieDetails[0].postAuthor + ' ' + movieDetails[0].postDate + '</p></div>';
                div2 = '<div class="main-news">';
                div3 = '<div class="big-news">';
                a1 = '<a href="">';
                img = '<img src="' + movieDetails[0].img + '" alt=""></a></div></div>';
                div4 = '<div class="info">';
                p2 = '<p>' + movieDetails[0].consoles + '</p>';
                p3 = '<p>Release Date: ' + movieDetails[0].releaseDate + '</p>';
                p4 = '<p>' + movieDetails[0].company + '</p>';
                p5 = '<p>' + movieDetails[0].esrb_rating + '</p></div>';
                div5 = '<div class="detail">';
                p6 = '<p>' + movieDetails[0].content + '</p></div>';
                div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';
                details = div1 + h2 + p1 + div2 + div3 + a1 + img + div4 + p2 + p3 + p4 + p5 + div5 + p6 + div6;
                hideModal();
                $('#showMovieDetails').html(details);
                $('#showMovieDetails').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#showMovieDetails').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#showMovieDetails').html("No Movie Detail Found");
            }
        });
    };
    $(document).on("click", ".GetMovieID", function ()
    {
        if(currentPage != '#movie-detail')
            pushCurrentPage(currentPage);
        showMoviesDetails($(this).attr("value"));
    });

    function showProfile()
    {
        $( "#TopHeader" ).show();
        currentPage = "#profile";
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        
        $.post( SERVER + "/WebServices/getUserProfileData.php",
        {
            
        }, function (data, status)
        {
            var jsonObj = data;
            var details = '';
            if (jsonObj.code == 0)
            {
                var profileDetails = jsonObj.response;

                $('#welcomeUser').html('Hello, ' + profileDetails[0].userDetails[3]);
                $('#profilePhoto').attr("src" , profileDetails[0].img);
                
                
                if(profileDetails[0].profiletype == 'public' )
                {
                    $("input[name=profileType][value=public]").prop('checked', true).checkboxradio("refresh");
                    //$("input[type='radio']").attr("checked",true).checkboxradio("refresh");
                }
                else
                {
                    $("input[name=profileType][value=private]").prop('checked', true).checkboxradio("refresh");
                }
                //$(".group1").checkboxradio("refresh");

                $('#me1').val(profileDetails[0].aboutMe[0]);
                $('#me2').val(profileDetails[0].aboutMe[1]);
                $('#me3').val(profileDetails[0].aboutMe[2]);

                var surveyDetails = '';
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    surveyDetails = surveyDetails + '<li>'+ profileDetails[0].surveyData[i].label +' <span class="ui-li-count">'+ profileDetails[0].surveyData[i].value +'</span></li>';
                }
                
                $('#YourSurveyResults').html(surveyDetails);
                

                $('#pu-name').val(profileDetails[0].userDetails[0]);
                $('#pf-name').val(profileDetails[0].userDetails[1]);
                $('#pl-name').val(profileDetails[0].userDetails[2]);
                $('#pd-name').val(profileDetails[0].userDetails[3]);
                $('#pemail').val(profileDetails[0].userDetails[4]);

                var birthday = new Date(profileDetails[0].userDetails[5]);
             
                var day = ("0" + birthday.getDate()).slice(-2);
                var month = ("0" + (birthday.getMonth() + 1)).slice(-2);

                var bd = birthday.getFullYear()+"-"+(month)+"-"+(day) ;

                $('#pdate').val(bd);

                if(profileDetails[0].userDetails[6] == 'm' )
                {
                    $("input[name=pgender][value=m]").prop('checked', true).checkboxradio("refresh");
                }
                else
                {
                    $("input[name=pgender][value=f]").prop('checked', true).checkboxradio("refresh");
                }
                hideModal();
                $('#profilePanelPhoto').attr("src" , profileDetails[0].img);
                $('#YourSurveyResults').listview('refresh');
                $('#profileLink').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#welcomeUser').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#welcomeUser').html("No Profile Detail Found");
            }
        });
    };

    $(document).on("click", "#profileLink", function ()
    {
        if(currentPage != '#profile')
            pushCurrentPage(currentPage);
        //switchPage(currentPage, false);
        showProfile();
    });

    $(document).on("click", "#showmyProfile", function ()
    {
        if(currentPage != '#profile')
            pushCurrentPage(currentPage);
        //switchPage(currentPage, false);
        showProfile();
    });

    function updateAboutMe(aboutMe, nowPlaying, gameTags)
    {
        $( "#TopHeader" ).show();
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        
        $.post( SERVER + "/WebServices/updateYourself.php",
        {
            me : aboutMe,
            playing : nowPlaying,
            tag : gameTags
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                alert("Your profile has been updated Successfully!")
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#welcomeUser').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#welcomeUser').html("No Profile Detail Found");
            }
        });
    };

    $(document).on("click", "#updateAboutMe", function ()
    {
        updateAboutMe($('#me1').val(),$('#me2').val(),$('#me3').val());
    });

    function ajaxFileUpload()
    {
        $.ajaxFileUpload
        (
            {
                url: SERVER + '/WebServices/uploadFile.php',
                secureuri:false,
                fileElementId:'profilePicture',
                dataType: 'json',
                data:{name:'logan', id:'id'},
                success: function (data, status)
                {
                    if(typeof(data.error) != 'undefined')
                    {
                        if(data.error != '')
                        {
                            alert(data.error);
                        }
                        else
                        {
                            alert(data.msg);
                        }
                    }
                },
                error: function (data, status, e)
                {
                    alert(e);
                }
            }
        )
        
        return false;

    }

    function updateProfile(f_name, l_name , d_name , e_mail , sex , dateOfBirth , old_pass , new_pass)
    {
        $( "#TopHeader" ).show();
        
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        
        var error = '';
        if (e_mail == '')
        {
            error = error + 'Enter your Email\n';
        }
        else if ($('#pdate').val() == '')
        {
            error = error + 'Enter correct "Date Of Birth"\n';
        }
        if ($('#pn-password').val() != $('#pr-password').val())
        {
            error = error + '"Re-Type Password" must match "Password"';
        }

        if(error == '')
        {
            showModal();
            var pictureType =  $('#profilePicture').val();
            pt = pictureType.split(".").pop().toLowerCase(); 
            if(!(pt == 'jpg' || pt == 'gif' || pt == 'jpeg' || pt == 'png' || pt == ''))
            {
                hideModal();
                alert("File Type not supported");
            }
            else
            {
                ajaxFileUpload();
                $.post( SERVER + "/WebServices/editProfile.php",
                {
                    fname : f_name,
                    lname : l_name,
                    dname : d_name,
                    email : e_mail,
                    gender : sex,
                    dob : dateOfBirth,
                    oldPass : old_pass,
                    newPass : new_pass
                }, function (data, status)
                {
                    var jsonObj = data;
                    if (jsonObj.code == 0)
                    {
                        hideModal();
                        setTimeout( function()
                        {
                            showProfile();
                        }, 3000 );
                        alert("Your profile has been updated Successfully!");

                    }
                    else if (jsonObj == -1)
                    {
                        hideModal();
                        $('#welcomeUser').html("DB Connectivity Failed");
                    }
                    else
                    {
                        hideModal();
                        $('#welcomeUser').html("No Profile Detail Found");
                    }
                });
            }
        }
        else
        {
            alert(error);
        }
    };

    $(document).on("click", "#updateProfile", function ()
    {
        updateProfile($('#pf-name').val(),$('#pl-name').val(),$('#pd-name').val(),$('#pemail').val(),$('input[name=pgender]:checked', '#updateProfileForm').attr("value"),$('#pdate').val(),$('#po-password').val(),$('#pn-password').val());
    });

    function changeProfileType(type)
    {
        $( "#TopHeader" ).show();
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        
        $.post( SERVER + "/WebServices/changePrivacy.php",
        {
            profileType : type
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                alert("Your Profile Type has been changed Successfully!");
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#welcomeUser').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#welcomeUser').html("No Profile Detail Found");
            }
        });
    };

    $("#getProfileStatus input:radio").click(function() 
    {
        changeProfileType($('input[name=profileType]:checked', '#ChangeProfileForm').attr("value"));
    });

    function retakeSurvey()
    {
        showModal();
        var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;
        
        $.post( SERVER + "/WebServices/getSurveyDataRetake.php",
        {
            
        }, function (data, status)
        {
            hideModal();
            
            var jsonObj = data;            
            if (jsonObj.code == 0)
            {
                var surveyData = jsonObj.response;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    for(var j = 1 ; j <= 10 ; j++)
                    {
                        //alert('#a'+ parseInt(j) + surveyData[i].id);
                        if($('#a'+ parseInt(j) + surveyData[i].id).attr('value') == surveyData[i].value )
                        {
                            //alert(surveyData[i].value);
                            $( "#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true ).checkboxradio( "refresh" );
                            break;
                        }
                    }
                }   
                // $('#surveyForm').trigger('create');
                // for(var i = 0 ; i < 8 ; i++)
                // {
                //     $('#surveyForm'+ parseInt(i)).trigger('create');
                // }
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("No Survey Details Found");
            }
        });
    };

    $(document).on("click", "#retakeSurvey", function ()
    {
        $( "#TopHeader" ).hide();
        $('#surveyForm').html('Survey is being Loaded. Please wait...');
        printSurvey();
        $('.saveDraft').html("Submit");
        $('.saveDraft').attr( "id", "submitSurvey" );
        for(var i = 0 ; i < 9 ; i++)
        {
            $('.survey')[i].reset();
        }
        retakeSurvey();
    });


    function showFriendSearchResults(sortby, searchQuery)
    {
        $( "#TopHeader" ).show();
        currentPage = 'members';
        showModal();
        var l1 , l2 , l3 , l4 , l5 , l6 , l7  , l9 , l10;
        $('#friendResults').html('');
        $.post( SERVER + "/WebServices/searchMembers.php",
        {
            criteria : sortby,
            key : searchQuery
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                var searchData = jsonObj.response;
                var searchResults = '';
                var totalFriends = 0;
                var totalMembers = jsonObj.rowCount;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    l1 = '<li data-icon="false">';
                    l2 = '<div class="li-img">';
                    l3 = '<a href=""><img src="'+ searchData[i].img+'"></a>';
                    l4 = '</div>';
                    l5 = '<div class="li-detail">';
                    l6 = '<a class="GetMemberInfo" value="'+ searchData[i].ID +'" href="#members-info"><h2>'+ searchData[i].display_name+'</h2></a>';
                    l7 = '<p class="c-name">'+ searchData[i].statusMsg+'</p>';
                    //l8 = '<p>Metal Gear Solid V: The Phantom Pain is a separated composite of two previously announced Kojima Productions projects, the both of which formed a complex deception.</p>';
                    l9 = '<p>'+ searchData[i].activeTime+'</p>';
                    
                    if("userID" in localStorage )
                    {
                        if(searchData[i].friendship_code == 0)
                            l10 = '';
                        else if(searchData[i].friendship_code == 1)
                        {
                            l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="1" class="ui-btn ui-btn-inline red friendshipOption">Cancel Friendship</a></div></li>';
                            totalFriends++;
                        }
                        else if(searchData[i].friendship_code == 2)
                            l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="2" class="ui-btn ui-btn-inline red friendshipOption">Cancel Friendship Request</a></div></li>';
                        else if(searchData[i].friendship_code == 3)
                            l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="3" class="ui-btn ui-btn-inline red friendshipOption">Accept</a><a id="'+searchData[i].ID+'" href="#" value="5" class="ui-btn ui-btn-inline red friendshipOption">Reject</a></div></li>';
                        else if(searchData[i].friendship_code == 4)
                            l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="4" class="ui-btn ui-btn-inline red friendshipOption">Add Friend</a></div></li>';
                    }
                    else
                    {
                        l10 = '';
                    }
                    searchResults = searchResults + l1 + l2 + l3 + l4 + l5 + l6 + l7  + l9 + l10;
                }   
                $('#totalMembers').html(totalMembers);
                $('#totalFriends').html(totalFriends);
                $('#friendResults').html(searchResults);
                $('#friendResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#friendResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#friendResults').html("No Friends Found");
            }
        });
    };

    $(document).on("click", "#searchFriends", function ()
    {
        //alert("searchFriends");
        if(currentPage != '#members')
            pushCurrentPage(currentPage);
        showFriendSearchResults($( "#select-choice-1 option:selected" ).attr("value"), $('#searchKey').val() );
    });

    $(document).on("click", "#searchMyFriends", function ()
    {
        //alert("searchMyFriends");
        //alert($( "#select-choice-1 option:selected" ).attr("value") + ' ' + $('#searchKey').val());
        if(currentPage != '#members')
            pushCurrentPage(currentPage);
        showFriendSearchResults($( "#select-choice-1 option:selected" ).attr("value") , $('#searchKey').val() );
    });

    function showMyFriends(sortby, searchQuery)
    {
        $( "#TopHeader" ).show();
        currentPage = 'members';
        showModal();
        var l1 , l2 , l3 , l4 , l5 , l6 , l7  , l9 , l10;
        $('#friendResults').html('');
        $.post( SERVER + "/WebServices/searchMembers.php",
        {
            criteria : sortby,
            key : searchQuery
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                var searchData = jsonObj.response;
                var searchResults = '';
                var totalFriends = 0;
                var totalMembers = jsonObj.rowCount;
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    if(searchData[i].friendship_code == 1)
                    {
                        l1 = '<li data-icon="false">';
                        l2 = '<div class="li-img">';
                        l3 = '<a href=""><img src="'+ searchData[i].img+'"></a>';
                        l4 = '</div>';
                        l5 = '<div class="li-detail">';
                        l6 = '<a class="GetMemberInfo" value="'+ searchData[i].ID +'" href="#members-info"><h2>'+ searchData[i].display_name+'</h2></a>';
                        l7 = '<p class="c-name">'+ searchData[i].statusMsg+'</p>';
                        //l8 = '<p>Metal Gear Solid V: The Phantom Pain is a separated composite of two previously announced Kojima Productions projects, the both of which formed a complex deception.</p>';
                        l9 = '<p>'+ searchData[i].activeTime+'</p>';
                        l10 = '<p><a id="'+ searchData[i].ID +'" href="#" value="1" class="ui-btn ui-btn-inline red friendshipOption">Cancel Friendship</a></div></li>';
                        totalFriends++;
                        searchResults = searchResults + l1 + l2 + l3 + l4 + l5 + l6 + l7  + l9 + l10;
                    }
                }   
                $('#totalMembers').html(totalMembers);
                //$('#totalFriends').html(totalFriends);
                $('#friendResults').html(searchResults);
                $('#friendResults').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#friendResults').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#friendResults').html("No Profile Detail Found");
            }
        });
    };

    $(document).on("click", "#showMyFriends", function ()
    {
        //alert("searchFriends");
        if(currentPage != '#members')
            pushCurrentPage(currentPage);
        showMyFriends('alphabetical', $('#searchKey').val() );
    });


    function friendshipActivity(fID , fCode)
    {
        $( "#TopHeader" ).show();
        showModal();
        
        $.post( SERVER + "/WebServices/friendshipActivity.php",
        {
            friendID : fID,
            code : fCode
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                var requestResult = jsonObj.response;
                if(requestResult[0].code == 5)
                {
                    alert("Rejected!");
                }
                else if(requestResult[0].code == 4)
                {
                    alert("Friend Request Sent!");
                }
                else if(requestResult[0].code == 3)
                {
                    alert("Accepted");
                }
                else if(requestResult[0].code == 2)
                {
                    alert("Friend Request Cancelled!");
                }
                else if(requestResult[0].code == 1)
                {
                    alert("Friendship Cancelled!");
                }
                showFriendSearchResults($( "#select-choice-1 option:selected" ).attr("value"), $('#searchKey').val() );
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });
    };


    $(document).on("click", ".friendshipOption", function ()
    {
        //alert($(this).attr("id") + " " + $(this).attr("value"))
        friendshipActivity($(this).attr("id"), $(this).attr("value"));
    });

    // $('#select-choice-1 option').click(function()
    // {
    //     alert("listClick");
    //     // alert($('#select-choice-1').selectedOptions().attr("value") + ' ' + $('#searchKey').val());
    //     // showFriendSearchResults($( "#select-choice-1 option:selected" ).attr("value") , $('#searchKey').val() );
    // });

    // // $("#select-choice-1 input:select").click(function() 
    // // {
    // //     alert("listClick");
    // // });

    function showMemberInfo(mID)
    {
        $( "#TopHeader" ).show();
        currentPage = 'members-info';
        showModal();
        
        $.post( SERVER + "/WebServices/getMemberInfo.php",
        {
            memberID : mID
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                var memberInfo = jsonObj.response;

                var l1 , l2 , l3 , l4 , l5 , l6 , l7 , l8 , l9 , l10 , l11 , l12 , l13 , l14 , l15 , l16;
                var memberActivities = '';

                $('#memberName').html( memberInfo[0].display_name );
                $('#memberUsername').html( memberInfo[0].username );
                $("#u-sname").val(memberInfo[0].username) ;
                $('#memberPicture').attr( "src", memberInfo[0].img );
                $('#lastMemberActiveTime').html( memberInfo[0].activeTime );
                $('#compareTop250').attr( "value", memberInfo[0].memberID );
                if(memberInfo[0].gender == 'm')
                    $('#memberGender').html( "Male" );
                else
                    $('#memberGender').html( "Female" );

                $('#memberAboutMe').html( memberInfo[0].aboutMe[0] );
                $('#memberPlaying').html( memberInfo[0].aboutMe[1] );
                $('#memberTags').html( memberInfo[0].aboutMe[2] );

                
                $('#publicMessageToMember').html( 'Public Message to ' + memberInfo[0].display_name );

                if("userID" in localStorage && memberInfo[0].ID != localStorage.getItem("userID"))
                {
                    if(memberInfo[0].friendship_code == 0)
                    {
                        $('.friendshipActivity').html('');
                        $( "a" ).remove( ".removeRejectButton" );
                    }
                    else if(memberInfo[0].friendship_code == 1)
                    {
                        //l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="1" class="ui-btn ui-btn-inline red friendshipOption">Cancel Friendship</a></div></li>';
                        $('.friendshipActivity').html('Cancel Friendship');
                        $('.friendshipActivity').attr("value" , "1");
                        $('.friendshipActivity').attr("id" , memberInfo[0].memberID );
                        $( "a" ).remove( ".removeRejectButton" );
                    }
                    else if(memberInfo[0].friendship_code == 2)
                    {
                        //l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="2" class="ui-btn ui-btn-inline red friendshipOption">Cancel Friendship Request</a></div></li>';
                        $('.friendshipActivity').html('Cancel Friendship Request');
                        $('.friendshipActivity').attr("value" , "2");
                        $('.friendshipActivity').attr("id" , memberInfo[0].memberID );
                        $( "a" ).remove( ".removeRejectButton" );
                    }
                    else if(memberInfo[0].friendship_code == 3)
                    {
                        //l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="3" class="ui-btn ui-btn-inline red friendshipOption">Accept</a><a id="'+searchData[i].ID+'" href="#" value="5" class="ui-btn ui-btn-inline red friendshipOption">Reject</a></div></li>';
                        $('.friendshipActivity').html('Accept');
                        $('.friendshipActivity').attr("value" , "3");
                        $('.friendshipActivity').attr("id" , memberInfo[0].memberID );

                        $( ".friendshipOptions" ).append( '<a id="'+ memberInfo[0].ID +'" value="5" href=""class="ui-btn ui-btn-b ui-btn-inline red friendshipActivity removeRejectButton">Reject</a>' );
                    }
                    else if(memberInfo[0].friendship_code == 4)
                    {
                        //l10 = '<p><a id="'+searchData[i].ID+'" href="#" value="4" class="ui-btn ui-btn-inline red friendshipOption">Add Friend</a></div></li>';
                        $('.friendshipActivity').html('Add Friend');
                        $('.friendshipActivity').attr("value" , "4");
                        $('.friendshipActivity').attr("id" , memberInfo[0].memberID );
                        $( "a" ).remove( ".removeRejectButton" );
                    }
                }
                else
                {
                    $('.friendshipActivity').html('');
                    $( "a" ).remove( ".removeRejectButton" );
                }

                var activity = memberInfo[0].newsfeed;
                var activityResults = '';
                var commentSection = '';
                var l1 , l2 , l3 , l4 , l5 , l6 , l7 , l8 , l9 , l10 , l11 , l12 , l13 , l14 , l15 , l16 , l17;
                var l0 = '';
                for (var i = 0; i < activity.length; i++)
                {
                    l1 = '<li data-icon="false" class="comment">';
                    l2 = '<div class="li-img">';
                    l3 = '<a><img src="' + activity[i].img + '"></a>';
                    l4 = '</div>';
                    l5 = '<div class="li-detail">';
                    l6 = '<h2>' + activity[i].action + '</h2>';
                    l7 = '<p class="c-name">' + activity[i].actionTime + '</p>';
                    l8 = '<p>' + activity[i].content + '</p>';
                    l9 = '<div class="likes">';
                    l10 = '<div class="scoring cf">';
                    l11 = '<div class="score"><span id="likesCount">' + activity[i].likes + '</span> <a class="thumbing" id="' + activity[i].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                    l12 = '<div class="score"><a class="thumbing" id="' + activity[i].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].dislikes + '</span></div>';
                    l13 = '<div class="score"style="margin-left: 1em;">';
                    if(activity[i].buttonCode == 2)
                    {
                        l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '<a href="" class="ui-btn ui-btn-inline red">Delete</a>';
                    }
                    else if(activity[i].buttonCode == 1)
                    {
                        l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '';
                    }
                    else
                    {
                        l14 = '';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '';
                    }  
                    l17 = '</div></div></div></div>';


                    activityResults = activityResults + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                    if(activity[i].comments.length > 0)
                    {
                        l0 = '<ul data-role="listview" data-inset="true" data-theme="b" class="post-reply subCommentRefresh">';
                    }
                    

                    for (var j = 0; j < activity[i].comments.length; j++)
                    {
                        l1 = '<li data-icon="false" class="reply">';
                        l2 = '<div class="li-img">';
                        l3 = '<a><img src="' + activity[i].comments[j].img + '"></a>';
                        l4 = '</div>';
                        l5 = '<div class="li-detail">';
                        l6 = '<h2>' + activity[i].comments[j].action + '</h2>';
                        l7 = '<p class="c-name">' + activity[i].comments[j].actionTime + '</p>';
                        l8 = '<p>' + activity[i].comments[j].content + '</p>';
                        l9 = '<div class="likes">';
                        l10 = '<div class="scoring cf">';
                        l11 = '<div class="score"><span id="likesCount">' + activity[i].comments[j].likes + '</span> <a class="thumbing" id="' + activity[i].comments[j].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                        l12 = '<div class="score"><a class="thumbing" id="' + activity[i].comments[j].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].comments[j].dislikes + '</span></div>';
                        l13 = '<div class="score"style="margin-left: 1em;">';
                        //if(activity[i].buttonCode == 2)
                        {
                            l14 = '';
                            l15 = '';
                            l16 = '';
                        }
                        // else if(activity[i].buttonCode == 1)
                        // {
                        //     l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     l16 = '';
                        // }
                        // else
                        // {
                        //     l14 = '';
                        //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     l16 = '';
                        // }  
                        l17 = '</div></div></div></div>';
                        if(j == 0)
                            activityResults = activityResults + l0 + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        else
                            activityResults = activityResults + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        l0 = '';

                        // if(activity[i].comments[j].subComments.length)
                        // {
                        //     l0 = '<ul data-role="listview" data-inset="true" data-theme="b">';
                        // }

                        // for (var k = 0; k < activity[i].comments[j].subComments.length; k++)
                        // {
                        //     l1 = '<li data-icon="false" class="reply">';

                        //     l2 = '<div class="li-img">';
                        //     l3 = '<a href=""><img src="' + activity[i].comments[j].subComments[k].img + '"></a>';

                        //     l4 = '</div>';
                        //     l5 = '<div class="li-detail">';
                        //     l6 = '<h2>' + activity[i].comments[j].subComments[k].action + '</h2>';
                        //     l7 = '<p class="c-name">' + activity[i].comments[j].subComments[k].actionTime + '</p>';
                        //     l8 = '<p>' + activity[i].comments[j].subComments[k].content + '</p>';
                        //     l9 = '<div class="likes">';
                        //     l10 = '<div class="scoring cf">';
                        //    l11 = '<div class="score"><span id="likesCount">' + activity[i].comments[j].subComments[k].likes + '</span> <a class="thumbing" id="' + activity[i].comments[j].subComments[k].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                        //    l12 = '<div class="score"><a class="thumbing" id="' + activity[i].comments[j].subComments[k].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].comments[j].subComments[k].dislikes + '</span></div>';
                        //     l13 = '<div class="score"style="margin-left: 1em;">';
                        //     //if(activity[i].buttonCode == 2)
                        //     {
                        //         l14 = '';
                        //         l15 = '';
                        //         l16 = '';
                        //     }
                        //     // else if(activity[i].buttonCode == 1)
                        //     // {
                        //     //     l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        //     //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     //     l16 = '';
                        //     // }
                        //     // else
                        //     // {
                        //     //     l14 = '';
                        //     //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     //     l16 = '';
                        //     // }  
                        //     l17 = '</div></div></div></div>';
                        //     activityResults = activityResults + l0 + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        //     l0 = '';

                        //     activityResults = activityResults + '</li>';
                        // //     if((k + 1) == activity[i].comments[j].subComments.length)
                        // //     {
                        // //         activityResults = activityResults + '</ul>';
                        // //     }
                        //  } 

                        activityResults = activityResults + '</li>';
                        if((j + 1) == activity[i].comments.length)
                        {
                            activityResults = activityResults + '</ul>';
                        }
                         
                    } 
                        commentSection = '<ul data-role="listview" data-inset="true" data-theme="b" class="post-reply"><div class="li-img"><a href=""><img src="images/download.jpg"></a></div><div class="li-detail"><form action=""><textarea name="" id="" ></textarea><button class="ui-btn ui-btn-inline red">Post</button><a href=""class="white cancel">Cancel</a></form></div></li></ul>';
                    activityResults = activityResults + commentSection + '</li>'; 
                }   
                
                $('#memberActivities').html(activityResults);
                $('#memberActivities').listview('refresh');
            

                // for(var i = 0 ; i < 2 /* jsonObj.rowCount */ ; i++)
                // {
                //     l1 = '<li data-icon="false">';
                //     l2 = '<div class="li-img">';
                //     l3 = '<a href=""><img src="images/download1.jpg"></a>';
                //     l4 = '</div>';
                //     l5 = '<div class="li-detail">';
                //     l6 = '<h2>Travis</h2>';
                //     l7 = '<p class="c-name">May 1,2014</p>';
                //     l8 = '<p>Metal Gear Solid V: The Phantom Pain is a separated composite of two previously announced Kojima Productions projects, the both of which formed a complex deception.</p>';
                //     l9 = '<div class="likes">';
                //     l10 = '<div class="scoring cf">';
                //     l11 = '<div class="score">1 <a href=""><span class="thumb-up"></span></a></div>';
                //     l12 = '<div class="score"><a href=""><span class="thumb-dn"></span></a>0</div>';
                //     l13 = '<div class="score"style="margin-left: 1em;">';
                //     l14 = '<a href="" class="ui-btn ui-btn-inline red count">Comment<span class="ui-li-count">12</span></a>';
                //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                //     l16 = '</div></div></div></div></li>';

                //     memberActivities = memberActivities + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8 + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16;
                // }
                // $('#memberActivities').html(memberActivities);
                // $('#memberActivities').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Could not fetch Activities");
            }
        });
    };

    
    $(document).on("click", ".GetMemberInfo", function ()
    {
        //alert($(this).attr("id") + " " + $(this).attr("value"));
        $("#postOnWall").attr("value" , $(this).attr("value"));

        if("userID" in localStorage)
        {
            $( "#postOnMemberWallArea" ).show();
            $( "#sign-in-to-post" ).hide();
        }
        else 
        {
            $( "#postOnMemberWallArea" ).hide();
            $( "#sign-in-to-post" ).show();                  
        }
        if(currentPage != '#members-info' )
            pushCurrentPage(currentPage);
        showMemberInfo($(this).attr("value"));
    });

    function postOnFriendsWall(fID , postContent)
    {
        $( "#TopHeader" ).show();
        showModal();
        
        $.post( SERVER + "/WebServices/postOnFriendWall.php",
        {
            friendID : fID,
            content : postContent
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                $("#postOnWallContent").val('');
                alert("Message Posted, Successfully!");
                showMemberInfo($("#postOnWall").attr("value"));
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });
    };

    $(document).on("click", "#postOnWall", function ()
    {
        
        postOnFriendsWall($("#postOnWall").attr("value"),$("#postOnWallContent").val());
    });


    function friendshipActivityFromMemberPage(fID , fCode)
    {
        $( "#TopHeader" ).show();
        showModal();
        
        $.post( SERVER + "/WebServices/friendshipActivity.php",
        {
            friendID : fID,
            code : fCode
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                hideModal();
                var requestResult = jsonObj.response;
                if(requestResult[0].code == 5)
                {
                    alert("Rejected!");
                }
                else if(requestResult[0].code == 4)
                {
                    alert("Friend Request Sent!");
                }
                else if(requestResult[0].code == 3)
                {
                    alert("Accepted");
                }
                else if(requestResult[0].code == 2)
                {
                    alert("Friend Request Cancelled!");
                }
                else if(requestResult[0].code == 1)
                {
                    alert("Friendship Cancelled!");
                }
                showMemberInfo(requestResult[0].ID);
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });
    };

    $(document).on("click", ".friendshipActivity", function ()
    {
        //alert($(this).attr("id") + " " + $(this).attr("value"));
        friendshipActivityFromMemberPage($(this).attr("id"), $(this).attr("value"));
    });

    
    function showActivities()
    {
        $( "#TopHeader" ).show();
        $( "#u-sname").val('');
        
        $.post( SERVER + "/WebServices/getMyNewsfeed.php",
        {
            
        }, function (data, status)
        {

            


            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                showModal();

                $.post( SERVER + "/WebServices/getUserProfileData.php",
                {
                    
                }, function (data, status)
                {
                    var jsonObj = data;
                    var details = '';
                    if (jsonObj.code == 0)
                    {
                        var profileDetails = jsonObj.response;

                        $('#myName').html( profileDetails[0].userDetails[3] );
                        $('#myUsername').html( profileDetails[0].userDetails[0] );
                        $('#myPicture').attr( "src", profileDetails[0].img );
                        $('#compareTop250').attr( "value", localStorage.getItem("ID") );
                        //$('#mylastMemberActiveTime').html( memberInfo[0].activeTime );
                        if(profileDetails[0].userDetails[6] == 'm')
                            $('#myGender').html( "Male" );
                        else
                            $('#myGender').html( "Female" );

                        $('#myAboutMe').html( profileDetails[0].aboutMe[0] );
                        $('#myPlaying').html( profileDetails[0].aboutMe[1] );
                        $('#myTags').html( profileDetails[0].aboutMe[2] );

                        hideModal();
                       
                    }
                    else if (jsonObj == -1)
                    {
                        hideModal();
                        $('#welcomeUser').html("DB Connectivity Failed");
                    }
                    else
                    {
                        hideModal();
                        $('#welcomeUser').html("No Profile Detail Found");
                    }
                });

                hideModal();
                var activity = jsonObj.response;
                var activityResults = '';
                var commentSection = '';
                var l1 , l2 , l3 , l4 , l5 , l6 , l7 , l8 , l9 , l10 , l11 , l12 , l13 , l14 , l15 , l16 , l17;
                var l0 = '';
                for (var i = 0; i < jsonObj.rowCount; i++)
                {
                    l1 = '<li data-icon="false" class="comment">';
                    l2 = '<div class="li-img">';
                    l3 = '<a><img src="' + activity[i].img + '"></a>';
                    l4 = '</div>';
                    l5 = '<div class="li-detail">';
                    l6 = '<h2>' + activity[i].action + '</h2>';
                    l7 = '<p class="c-name">' + activity[i].actionTime + '</p>';
                    l8 = '<p>' + activity[i].content + '</p>';
                    l9 = '<div class="likes">';
                    l10 = '<div class="scoring cf">';
                    l11 = '<div class="score"><span id="likesCount">' + activity[i].likes + '</span> <a class="thumbing" id="' + activity[i].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                    l12 = '<div class="score"><a class="thumbing" id="' + activity[i].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].dislikes + '</span></div>';
                    l13 = '<div class="score"style="margin-left: 1em;">';
                    if(activity[i].buttonCode == 2)
                    {
                        l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '<a href="" class="ui-btn ui-btn-inline red">Delete</a>';
                    }
                    else if(activity[i].buttonCode == 1)
                    {
                        l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '';
                    }
                    else
                    {
                        l14 = '';
                        l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        l16 = '';
                    }  
                    l17 = '</div></div></div></div>';


                    activityResults = activityResults + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                    if(activity[i].comments.length > 0)
                    {
                        l0 = '<ul data-role="listview" data-inset="true" data-theme="b" class="post-replysubCommentRefresh">';
                    }
                    

                    for (var j = 0; j < activity[i].comments.length; j++)
                    {
                        l1 = '<li data-icon="false" class="reply">';
                        l2 = '<div class="li-img">';
                        l3 = '<a><img src="' + activity[i].comments[j].img + '"></a>';
                        l4 = '</div>';
                        l5 = '<div class="li-detail">';
                        l6 = '<h2>' + activity[i].comments[j].action + '</h2>';
                        l7 = '<p class="c-name">' + activity[i].comments[j].actionTime + '</p>';
                        l8 = '<p>' + activity[i].comments[j].content + '</p>';
                        l9 = '<div class="likes">';
                        l10 = '<div class="scoring cf">';
                        l11 = '<div class="score"><span id="likesCount">' + activity[i].comments[j].likes + '</span> <a class="thumbing" id="' + activity[i].comments[j].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                        l12 = '<div class="score"><a class="thumbing" id="' + activity[i].comments[j].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].comments[j].dislikes + '</span></div>';
                        l13 = '<div class="score"style="margin-left: 1em;">';
                        //if(activity[i].buttonCode == 2)
                        {
                            l14 = '';
                            l15 = '';
                            l16 = '';
                        }
                        // else if(activity[i].buttonCode == 1)
                        // {
                        //     l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     l16 = '';
                        // }
                        // else
                        // {
                        //     l14 = '';
                        //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     l16 = '';
                        // }  
                        l17 = '</div></div></div></div>';
                        if(j == 0)
                            activityResults = activityResults + l0 + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        else
                            activityResults = activityResults + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        l0 = '';

                        // if(activity[i].comments[j].subComments.length)
                        // {
                        //     l0 = '<ul data-role="listview" data-inset="true" data-theme="b">';
                        // }

                        // for (var k = 0; k < activity[i].comments[j].subComments.length; k++)
                        // {
                        //     l1 = '<li data-icon="false" class="reply">';

                        //     l2 = '<div class="li-img">';
                        //     l3 = '<a href=""><img src="' + activity[i].comments[j].subComments[k].img + '"></a>';

                        //     l4 = '</div>';
                        //     l5 = '<div class="li-detail">';
                        //     l6 = '<h2>' + activity[i].comments[j].subComments[k].action + '</h2>';
                        //     l7 = '<p class="c-name">' + activity[i].comments[j].subComments[k].actionTime + '</p>';
                        //     l8 = '<p>' + activity[i].comments[j].subComments[k].content + '</p>';
                        //     l9 = '<div class="likes">';
                        //     l10 = '<div class="scoring cf">';
                        //    l11 = '<div class="score"><span id="likesCount">' + activity[i].comments[j].subComments[k].likes + '</span> <a class="thumbing" id="' + activity[i].comments[j].subComments[k].ID + '" value="1" href=""><span class="thumb-up"></span></a></div>';
                        //    l12 = '<div class="score"><a class="thumbing" id="' + activity[i].comments[j].subComments[k].ID + '" value="0" href=""><span class="thumb-dn"></span></a><span id="dislikesCount">' + activity[i].comments[j].subComments[k].dislikes + '</span></div>';
                        //     l13 = '<div class="score"style="margin-left: 1em;">';
                        //     //if(activity[i].buttonCode == 2)
                        //     {
                        //         l14 = '';
                        //         l15 = '';
                        //         l16 = '';
                        //     }
                        //     // else if(activity[i].buttonCode == 1)
                        //     // {
                        //     //     l14 = '<a href="" class="ui-btn ui-btn-inline red comment-btn">Comment</a>';
                        //     //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     //     l16 = '';
                        //     // }
                        //     // else
                        //     // {
                        //     //     l14 = '';
                        //     //     l15 = '<a href="" class="ui-btn ui-btn-inline red">Favourite</a>';
                        //     //     l16 = '';
                        //     // }  
                        //     l17 = '</div></div></div></div>';
                        //     activityResults = activityResults + l0 + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8  + l9 + l10 + l11 + l12 + l13 + l14 + l15 + l16 + l17;
                        //     l0 = '';

                        //     activityResults = activityResults + '</li>';
                        // //     if((k + 1) == activity[i].comments[j].subComments.length)
                        // //     {
                        // //         activityResults = activityResults + '</ul>';
                        // //     }
                        //  } 

                        activityResults = activityResults + '</li>';
                        if((j + 1) == activity[i].comments.length)
                        {
                            activityResults = activityResults + '</ul>';
                        }
                         
                    } 
                    commentSection = '<ul data-role="listview" data-inset="true" data-theme="b" class="post-reply"><div class="li-img"><a href=""><img src="images/download.jpg"></a></div><div class="li-detail"><form action=""><textarea name="" id="" ></textarea><button class="ui-btn ui-btn-inline red">Post</button><a href=""class="white cancel">Cancel</a></form></div></li></ul>';
                    activityResults = activityResults + commentSection + '</li>'; 
                }   
                
                $(".post-reply").hide();
                $('#userActivities').html(activityResults);
                $('#userActivities').listview('refresh');
                //$('.post-reply').listview('refresh');
                //$('.subCommentRefresh').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });

        

    };

    $(document).on("click", "#activity", function ()
    {
        //alert($(this).attr("id") + " " + $(this).attr("value"));
        $('#userActivities').html('');
        showActivities();
    });



    $(document).on("click", ".thumbing", function ()
    {
        //alert($(this).attr("id") + " " + $(this).attr("value"));
        $( "#TopHeader" ).show();
        
        var pID = $(this).attr("id");
        var vType = $(this).attr("value");
        
        $.post( SERVER + "/WebServices/thumbsUpDown.php",
        {
            postID  : pID,
            votetype : vType
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                showActivities();
            }
            else if (jsonObj == -1)
            {
                
                alert("DB Connectivity Failed");
            }
            else
            {
                
                alert("Request Failed");
            }
        });
    });

    $(document).on("click", "#updateActivity", function ()
    {
        $( "#TopHeader" ).show();
        
        var content = $('#status').val();
        
        $.post( SERVER + "/WebServices/statusUpdate.php",
        {
            content  : content
        }, function (data, status)
        {
            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                $('#status').val('');
                alert('Status Updated!');
                showActivities();
            }
            else if (jsonObj == -1)
            {
                
                alert("DB Connectivity Failed");
            }
            else
            {
                
                alert("Request Failed");
            }
        });
    });

    function sendMessage(friendID , message , sub)
    {
        $( "#TopHeader" ).show();
        
        
        $.post( SERVER + "/WebServices/sendMsg.php",
        {
            username : friendID,
            content : message,
            subject : sub
        }, function (data, status)
        {

            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                $("#u-sname").val('') ; $("#t-area").val('') ; $("#subject").val('');
                alert("Message Sent!");
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Message Sending Failed");
            }
        });

        

    };

    $(document).on("click", "#sendMessage", function ()
    {
        
        sendMessage($("#u-sname").val() , $("#t-area").val() , $("#subject").val() );
    });

    $(document).on("click", "#msendMessage", function ()
    {
        
        sendMessage($("#mu-sname").val() , $("#mt-area").val() , $("#msubject").val() );
    });


    function CheckInbox()
    {
        $( "#TopHeader" ).show();
        
        
        $.post( SERVER + "/WebServices/checkInbox.php",
        {
            friendID : friendID,
            content : message
        }, function (data, status)
        {

            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });

        

    };

    $(document).on("click", "#inbox", function ()
    {
        CheckInbox();
    });

    function CheckSent()
    {
        $( "#TopHeader" ).show();
        
        
        $.post( SERVER + "/WebServices/getSentBox.php",
        {
            
        }, function (data, status)
        {

            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                var msgData = jsonObj.response;
                var sentMessages = '';
                $('#sentTexts').html('');
                var l1 , l2 , l3 , l4 , l5 , l6 , l7 , l8 ;
                for (var i = 0; i < jsonObj.rowCount ; i++)
                {
                    l1 = '<li data-icon="false">';
                    l2 = '<a id="'+ msgData[i].threadID +'" class="getSentThread" href="#">';
                    l3 = '<div class="li-img">';
                    l4 = '<img src="'+ msgData[i].img+'"></div>';
                    l5 = '<div class="li-detail">';
                    l6 = '<h2>'+ msgData[i].senderName +'</h2>';
                    l7 = '<p>'+ msgData[i].msg +'</p>';
                    l8 = '<p class="c-name">'+ msgData[i].msgDate +'</p></div></a></li>';

                    sentMessages = sentMessages + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8;
                }

                hideModal();
                $('#sentTexts').html(sentMessages);
                $('#sentTexts').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });

        

    };

    $(document).on("click", "#sentBox", function ()
    {
        CheckSent();
    });

    function getThread(tID)
    {
        $( "#TopHeader" ).show();
        
        
        $.post( SERVER + "/WebServices/getThreadDetails.php",
        {
            threadID : tID
        }, function (data, status)
        {

            var jsonObj = data;
            if (jsonObj.code == 0)
            {
                var msgData = jsonObj.response;
                var sentMessages = '';
                $('#sentTexts').html('');
                var l1 , l2 , l3 , l4 , l5 , l6 , l7 , l8 ;
                for (var i = 0; i < jsonObj.rowCount ; i++)
                {
                    l1 = '<li data-icon="false">';
                    l2 = '<a id="'+ msgData[i].threadID +'" href="#">';
                    l3 = '<div class="li-img">';
                    l4 = '<img src="'+ msgData[i].img+'"></div>';
                    l5 = '<div class="li-detail">';
                    l6 = '<h2>'+ msgData[i].senderName +'</h2>';
                    l7 = '<p>'+ msgData[i].msg +'</p>';
                    l8 = '<p class="c-name">'+ msgData[i].msgDate +'</p></div></a></li>';

                    sentMessages = sentMessages + l1 + l2 + l3 + l4 + l5 + l6 + l7 + l8;
                }

                hideModal();
                $('#sentTexts').html(sentMessages);
                $('#sentTexts').listview('refresh');
            }
            else if (jsonObj == -1)
            {
                hideModal();
                alert("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                alert("Request Failed");
            }
        });

        

    };

    $(document).on("click", ".getSentThread", function ()
    {
        getThread($(this).attr("id"));
    });

    function compareTop250(name,ID)
    {
        $( "#TopHeader" ).show();
        currentPage = '#top-250';
        $('#paginate250').html('');
        showModal();

        $('#CalculatedGamesList').html(' ');
        $('#Top250GamesHeading').html(name + ' Top 250 Games');
        var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
        $.post( SERVER + "/WebServices/getUserTopGames.php",
        {
            userID: ID,
            limit: 250
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = 1; i <= jsonObj.rowCount && i <= 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                var pagination = '';
                pagination = '<a value="0" class="page red ui-btn ypf">« First</a>';
                var i = 1;
                for( i = 1 ; i < (jsonObj.rowCount/25)-1 ; i++)
                {
                    pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn ypf">'+parseInt(i+1)+'</a>';                    
                }

                pagination = pagination + '<a value="'+parseInt(i*25)+'"  class="page red ui-btn ypf">Last »</a>';

                hideModal();
                $('#CalculatedGamesList').html(game);
                $('#CalculatedGamesList').listview('refresh');

                $('#paginate250').html(pagination);
                
                

            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#CalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#CalculatedGamesList').html('<p>No Games Found to Compare</p>');
                
            }
        });
    };

    $(document).on("click", "#compareTop250", function ()
    {
        if(currentPage != '#top-250')
            pushCurrentPage(currentPage);
        if($('#compareTop250').attr("value") == localStorage.getItem("ID"))
            compareTop250("Your" , $('#compareTop250').attr("value")); 
        else
            compareTop250( $('#memberUsername').text() + "'s" , $('#compareTop250').attr("value"));

    }); 

    function showNextinFriend250(ID)
    {
        $( "#TopHeader" ).show();
        $('#CalculatedGamesList').html(' ');
        showModal();
        $.post( SERVER + "/WebServices/getUserTopGames.php",
        {
            userID: ID,
            limit: 250
        }, function (data, status)
        {
            var jsonObj = data;
            var game = '';
            if (jsonObj.code == 0)
            {
                var gameData = jsonObj.response;
                for (var i = limit + 1; i <= jsonObj.rowCount && i <= limit + 25; i++)
                {
                    li = '<li data-icon="false">';
                    a = '<a class="GetPostID" id="TopGame' + parseInt(i) + '" href="#game-detail" value="' + gameData[i - 1].postID + '">';
                    //div_img = '';
                    img = '<div class="li-img"><img src="' + gameData[i - 1].img + '">';
                    span1 = '<span class="discrip">' + i + '</span></div>';
                    h2 = '<div class="li-detail"><h2>&nbsp ' + gameData[i - 1].title + '</h2>';
                    p1 = '<p class="limitText">&nbsp ' + gameData[i - 1].content + '</p>';
                    p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                    var therm = parseInt(((parseInt(gameData[i - 1].rating)) / 10));
                    if (parseInt(((parseInt(gameData[i - 1].rating)) / 10)) >= 100)
                    {
                        div = '<div class="meter-full">';
                        thermometer = '<div class="thermometer"></div></div></a></li>';
                    }
                    else
                    {
                        div = '<div class="meter">';
                        thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';
                    }
                    span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';
                    game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;
                }
                
                hideModal();
                $('#CalculatedGamesList').html(game);
                $('#CalculatedGamesList').listview('refresh');              
            }
            else if (jsonObj == -1)
            {
                hideModal();
                $('#CalculatedGamesList').html("DB Connectivity Failed");
            }
            else
            {
                hideModal();
                $('#CalculatedGamesList').html("No Game Found");
                $('#paginate250').html("");
            }
        });

    };

    $(document).on("click", ".ypf", function ()
    {
        limit = parseInt($(this).attr("value"));
        showNextinFriend250($('#compareTop250').attr("value")); 
    }); 


});
