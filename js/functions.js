$(document).ready(function()
{
        getTop10Games();

        function getTop10Games()
        {
            loading('show');

            $('#mainTop10').html(' ');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post("http://meterbreak.triapptech.com/WebServices/getTopTenGames.php",
            {
                platform: 'all'
            },
            function(data, status)
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
                        p1 = '<p>&nbsp ' + gameData[i - 1].content + '...</p>';
                        p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';
                        var therm = parseInt(((gameData[i - 1].rating) / 10));

                        if (gameData[i - 1].rating >= 90)
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

                    loading('hide');

                    $('#mainTop10').html(game);

                    $('#mainTop10').listview('refresh');
                } 
                else if (jsonObj == -1)
                {
                    loading('hide');
                    $('#mainTop10').html("DB Connectivity Failed");
                } 
                else
                {
                    loading('hide');
                    $('#mainTop10').html("No Game Found");
                }

            });
        };

        function loading(showOrHide)
        {
            $("body").append('<div class="modalWindow"></div>');
            setTimeout(function()
            {
                if (showOrHide == 'hide')
                {
                    $(".modalWindow").remove();
                }
                $.mobile.loading(showOrHide);
            }, 1);
        };


        function getTopTenGames(category, type)
        {

            loading('show');

            $('#TopGamesHeading').html('Top 10 ' + type + ' Games');

            $('#top10Games').html(' ');

            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;

            $.post("http://meterbreak.triapptech.com/WebServices/getTopTenGames.php",

                {

                    platform: category

                },

                function(data, status)

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

                            p1 = '<p>&nbsp ' + gameData[i - 1].content + '...</p>';

                            p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';

                            var therm = parseInt(((gameData[i - 1].rating) / 10));

                            if (gameData[i - 1].rating >= 90)

                            {

                                div = '<div class="meter-full">';

                                thermometer = '<div class="thermometer"></div></div></a></li>';

                            } else

                            {

                                div = '<div class="meter">';

                                thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';

                            }

                            span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';



                            game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;

                        }

                        loading('hide');

                        $('#top10Games').html(game);

                        $('#top10Games').listview('refresh');



                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#top10Games').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#top10Games').html("No Game Found");

                    }

                });



        };

		$(document).on("click", "#barcode", function()
		 {

		 var scanner = cordova.plugins.barcodeScanner;

		scanner.scan(
			function (result) {
			alert("We got a barcode\n" +
			"Result: " + result.text + "\n" +
			"Format: " + result.format + "\n" +
			"Cancelled: " + result.cancelled);
			}, 
			function (error) {
			alert("Scanning failed: " + error);
			}
			);
		}
		   );


        $(document).on("click", ".GetGames", function()

            {

                getTopTenGames($(this).attr("id"), $(this).attr("value"));

            });




        function showGameDetails(post_id)

        {

            loading('show');

            var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;

            $('#showDetails').html(' ');

            $.post("http://meterbreak.triapptech.com/WebServices/getDetailsOfGame.php",

                {

                    id: post_id

                },

                function(data, status)

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

                        p4 = '<p>' + gameDetails[0].company + '</p>'

                        p5 = '<p>' + gameDetails[0].esrb_rating + '</p></div>';

                        div5 = '<div class="detail">';

                        p6 = '<p>' + gameDetails[0].content + '</p></div>';

                        div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';



                        details = div1 + h2 + p1 + div2 + div3 + a1 + img + div4 + p2 + p3 + p4 + p5 + div5 + p6 + div6;



                        loading('hide');

                        $('#showDetails').html(details);

                        $('#showDetails').listview('refresh');



                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#showDetails').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#showDetails').html("No Game Details Found");

                    }

                });

        };



        $(document).on("click", ".GetPostID", function() {

            showGameDetails($(this).attr("value"));

        });




        /******************************/



        function getTopNews(category, type)

        {

            loading('show');

            $('#mainNews').html(' ');

            $('#moreNews').html(' ');

            $('#otherNews').html(' ');

            $.post("http://meterbreak.triapptech.com/WebServices/getNews.php",

                {

                    platform: category

                },

                function(data, status)

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

                            a1 = '<a class="GetNewsID" href="#game-detail" value="' + newsData[0].postID + '">';

                            img1 = '<img src="' + newsData[0].img + '" alt="">';

                            span1 = '<span class="news-discrip">';

                            p1 = '<p><strong>' + newsData[0].title + '</strong></p>';

                            p2 = '<p class="sm">' + newsData[0].content + '...</p>';

                            p3 = '<p class="ex-sm">' + newsData[0].postDate + ' | ' + newsData[0].postAuthor + '</p></span></a></div>';



                            top3News = div1 + a1 + img1 + span1 + p1 + p2 + p3;

                            index = 1;

                        }

                        if (jsonObj.rowCount >= 4)

                        {

                            div2 = '<div class="small-news">';

                            div3 = '<div class="news"style="margin-right: .5%;">';

                            a2 = '<a class="GetNewsID" href="#game-detail" value="' + newsData[1].postID + '">';

                            img2 = '<img src="' + newsData[1].img + '" alt="">';

                            span2 = '<span class="news-discrip">';

                            p4 = '<p><strong>' + newsData[1].title + '</strong></p>';

                            p5 = '<p class="sm">' + newsData[1].content + '...</p>';

                            p6 = '<p class="ex-sm">' + newsData[1].postDate + ' | ' + newsData[1].postAuthor + '</p></span></a></div>';



                            top3News = div1 + a1 + img1 + span1 + p1 + p2 + p3 + div2 + div3 + a2 + img2 + span2 + p4 + p5 + p6 + '</div>';

                            index = 2;

                        }

                        if (jsonObj.rowCount > 5)

                        {

                            div4 = '<div class="news"style="margin-left: .5%;">';

                            a3 = '<a class="GetNewsID" href="#game-detail" value="' + newsData[2].postID + '">';

                            img3 = '<img src="' + newsData[2].img + '" alt="">';

                            span3 = '<span class="news-discrip">';

                            p7 = '<p><strong>' + newsData[2].title + '</strong></p>';

                            p8 = '<p class="sm">' + newsData[2].content + '...</p>';

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

                            details = '<a class="GetNewsID" href="#game-detail" value="' + newsData[i].postID + '">';

                            thumbnail = '<div class="li-img"><img src="' + newsData[i].img + '">';

                            span4 = '<span class="discrip">' + parseInt(i - 2) + '</span></div>';

                            heading = '<div class="li-detail"><h2>' + newsData[i].title + '</h2>';

                            content = '<p>' + newsData[i].content + '...</p>';

                            nameDate = '<p class="c-name">' + newsData[i].postDate + ' | ' + newsData[i].postAuthor + '</p></div></a></li>';



                            subNews = subNews + li + details + thumbnail + span4 + heading + content + nameDate;

                        }



                        $('#moreNews').html("More News Stories");

                        $('#otherNews').html(subNews);

                        $('#otherNews').listview('refresh');

                        loading('hide');

                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#mainNews').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#mainNews').html("No News Found");

                    }



                });

        };



        $(document).on("click", ".GetNews", function() {

            getTopNews($(this).attr("id"), $(this).attr("value"));

        });



        function showNewsDetails(post_id)
        {

            loading('show');

            var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;

            $('#showDetails').html(' ');

            $.post("http://meterbreak.triapptech.com/WebServices/getDetailsOfNews.php",

                {

                    id: post_id

                },

                function(data, status)

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

                            if (i != (jsonObj.rowCount - 1)) {

                                p2 = p2 + newsDetails[0].categories[i] + ',';
                            } else {

                                p2 = p2 + newsDetails[0].categories[i] + '</p>';
                            }

                        }

                        div5 = '</div><div class="detail">';

                        p3 = '<p>' + newsDetails[0].content + '</p></div>';

                        div6 = '<div class="ui-grid-b" data-theme="b"><div class="ui-block-a"><a href=""class="ui-btn ui-btn-b">FAQs</a></div><div class="ui-block-b"><a href=""class="ui-btn ui-btn-b">Videos</a></div><div class="ui-block-c"><a href=""class="ui-btn ui-btn-b">Picture</a></div></div>';



                        details = div1 + h2 + p1 + div2 + div3 + a1 + img + div4 + p2 + div5 + p3 + div6;

                        loading('hide');

                        $('#showDetails').html(details);

                        $('#showDetails').listview('refresh');



                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#showDetails').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#showDetails').html("No Game Details Found");

                    }

                });

        };



        $(document).on("click", ".GetNewsID", function() {

            showNewsDetails($(this).attr("value"));

        });


        function getMeltingPoint()
        {
            
            loading('show');

            $('#mps').html(' ');

            var li, a, h2 , p1 , p2;

            $.post("http://meterbreak.triapptech.com/WebServices/getMeltingPointStories.php",
                {
                    platform: "fjsdk"
                },

                function(data, status)
                {
                   
                    var jsonObj = data;

                    var meltingpoints = '';

                    if (jsonObj.code == 0)
                    {
                        var mpData = jsonObj.response;

                        for (var i = 0; i < jsonObj.rowCount; i++)
                        {

                            li = '<li data-icon="false">';

                            a = '<a class="GetMP" href="#game-detail" value="'+ mpData[i].postID +'">';

                            h2 = '<h2>'+ mpData[i].title +'</h2>';

                            p1 = '<p>'+ mpData[i].content +'...</p>';

                            p2 = '<p class="c-name">'+ mpData[i].postDate +' | '+ mpData[i].postAuthor +'</p></a></li>';

                            meltingpoints = meltingpoints + li + a + h2 + p1 + p2 ;

                        }

                        loading('hide');

                        $('#mps').html(meltingpoints);
                        $('#mps').listview('refresh');



                    } 
                    else if (jsonObj == -1)
                    {
                        loading('hide');
                        $('#mps').html("DB Connectivity Failed");
                    } 
                    else
                    {
                        loading('hide');
                        $('#mps').html("No Game Found");
                    }
                });



        };

        $(document).on("click", "#meltingp", function() 
        {
            
            getMeltingPoint();
        });

        function showMPStory(post_id)
        {
            loading('show');

            var div1, h2, p1, div2, div3, a1, img, div4, p2, p3, p4, p5, div5, p6, div6;

            $('#showDetails').html(' ');

            $.post("http://meterbreak.triapptech.com/WebServices/getMeltingPointStoryDetail.php",
                {
                    id:post_id
                },

                function(data, status)
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

                        details = div1 + h2 + p1 + div2 + div3 + a1  + div4 + div5 + p3 + div6;

                        loading('hide');

                        $('#showDetails').html(details);

                        $('#showDetails').listview('refresh');



                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#showDetails').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#showDetails').html("No Game Details Found");

                    }

                });

        };

        $(document).on("click", ".GetMP", function() {

            showMPStory($(this).attr("value"));

        });

        var catType = 'all';

        function showSearchResults(category)
        {

            loading('show');
            catType = category;
            $('#searchResults').html(' ');

            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;

            $.post("http://meterbreak.triapptech.com/WebServices/searchGames.php",
                {

                    platform: category

                },
                function(data, status)
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

                            p1 = '<p>&nbsp ' + gameData[i - 1].content + '...</p>';

                            p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';

                            var therm = parseInt(((gameData[i - 1].rating) / 10));

                            if (gameData[i - 1].rating >= 90)

                            {

                                div = '<div class="meter-full">';

                                thermometer = '<div class="thermometer"></div></div></a></li>';

                            } else

                            {

                                div = '<div class="meter">';

                                thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';

                            }

                            span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';



                            game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;

                        }

                        loading('hide');

                        $('#searchResults').html(game);

                        $('#searchResults').listview('refresh');



                    } else if (jsonObj == -1)

                    {

                        loading('hide');

                        $('#searchResults').html("DB Connectivity Failed");

                    } else

                    {

                        loading('hide');

                        $('#searchResults').html("No Game Found");

                    }

                });



        };

        


         $(document).on("click", ".searchCategory", function() {

            showSearchResults($(this).attr("value"));

        });


         function showSearchResultsByApha(alpha)
        {

            loading('show');
            $('#searchResults').html(' ');

            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;

            $.post("http://meterbreak.triapptech.com/WebServices/searchGames.php",
                {

                    platform: catType,
                    alphabet: alpha

                },
                function(data, status)
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

                            p1 = '<p>&nbsp ' + gameData[i - 1].content + '...</p>';

                            p2 = '<p class="c-name"> &nbsp&nbsp' + gameData[i - 1].postDate + ' | ' + gameData[i - 1].postAuthor + '</p></div>';

                            var therm = parseInt(((gameData[i - 1].rating) / 10));

                            if (gameData[i - 1].rating >= 90)

                            {

                                div = '<div class="meter-full">';

                                thermometer = '<div class="thermometer"></div></div></a></li>';

                            } else

                            {

                                div = '<div class="meter">';

                                thermometer = '<div class="thermometer-' + therm + ' thermometer-main"></div>';

                            }

                            span2 = '<span class="ui-li-count">' + gameData[i - 1].rating + '</span>';



                            game = game + li + a + img + span1 + h2 + p1 + p2 + div + span2 + thermometer;

                        }

                        loading('hide');

                        $('#searchResults').html(game);

                        $('#searchResults').listview('refresh');



                    } 
                    else if (jsonObj == -1)
                    {
                        loading('hide');
                        $('#searchResults').html("DB Connectivity Failed");
                    } 
                    else
                    {
                        loading('hide');
                        $('#searchResults').html("No Game Found");
                    }

                });
        };

        $(document).on("click", ".searchCatAlpha", function() 
         {
            showSearchResultsByApha($(this).text());
        });

        $(document).on("click", "#searchAll", function() 
         {
            showSearchResults('all');
        });


        function showForums()
        {
            loading('show');

            $('#forumsList').html(' ');

            var li, a , h2;

                $.post("http://meterbreak.triapptech.com/WebServices/getForumCategories.php",
                {
                    
                },
                function(data, status)
                {
                    var jsonObj = data;

                    var forum = '';

                    if (jsonObj.code == 0)
                    {

                        var forumData = jsonObj.response;

                        for (var i = 0; i < jsonObj.rowCount; i++)
                        {
                            li  = '<li data-icon="false">';
                            a = '<a class="GetForumID" href="#" value="' + forumData[i].postID + '">';
                            h2  = '<h2>' + forumData[i].title + '</h2></a></li>';
                            
                            forum = forum + li + a + h2;
                        }

                        loading('hide');

                        $('#forumsList').html(forum);

                        $('#forumsList').listview('refresh');

                    } 
                    else if (jsonObj == -1)
                    {
                        loading('hide');
                        $('#forumsList').html("DB Connectivity Failed");

                    } 
                    else
                    {
                        loading('hide');
                        $('#forumsList').html("No Forum Found");
                    }
                });
        };

        $(document).on("click", "#showForums", function() 
        {
            showForums();
        });

});