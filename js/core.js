
	var currentPage = "#home";
    //var isStart = true;
    var pageStack = [];
    var firstTime = true;
    var SurveyComplete = false;

    var SERVER =  "http://meterbreak.com/app";

	document.addEventListener("deviceready", onDeviceReady, false);
	document.addEventListener("backbutton", onBackPressed, false);

    

	function onDeviceReady() 
	{
     	$( "#TopHeader" ).show();	
    	if("userID" in localStorage )
    	{
    		var uID = localStorage.getItem("userID");
    		var uPass = localStorage.getItem("userPassword");
    		$.post( SERVER + "/WebServices/Login.php",
	        {
	            username: uID,
	            password: uPass
	        }, function (data, status)
	        {
                
                
	            var jsonObj = data;
	            
	            if (jsonObj.code == 0)
	            {
	                loggedIn = 0;
	                
	                var profileData = jsonObj.response;
	                headerProfile = '<a href="#profile"class="ui-btn user-pic-name">';
	                imageProfile = '<img id="profilePanelPhoto" src="'+ profileData[0].img +'" alt="" class="ui-li-icon">'+ profileData[0].display_name +'</a>';	                
	                
	                $('#profileLink').html( headerProfile + imageProfile);
	                $('#loginlogout').html( '<a id="logout" href="#home" class="ui-btn ui-btn-icon-right ui-icon-carat-r">Sign Out</a>' );
                    $('#profileLink').listview('refresh');
	                $('#loginlogout').listview('refresh');

	            }
	            else if (jsonObj.code == -1)
	            {
	                Toast.longshow("Connection Error");
	            }
	            else
	            {
	                Toast.longshow("Connection Error");
	            }
	        });

            
    	}

        if("userID" in localStorage)
        {
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
                    alert("Please complete the survey by selecting [Button with 3 lines] and then selecting \"Survey\". Thank you!");
                }
                else if(jsonObj.code == 1)
                {
                    alert("Please fill in the survey from the Left panel!\nThank You");
                }
                         
                if (jsonObj.code == 2)
                {
                    SurveyComplete = true;
                    $('#surveyInPanel').html('');
                               
                } 
                else
                {
                    $('#surveyInPanel').html('<a id="doSurvey" href="#survey" class="ui-btn ui-btn-icon-right ui-icon-carat-r gamePanel">Survey</a>');
                    $('#surveyInPanel').listview('refresh');   
                }


                 
            });
        }
        
        

	}

    function showModal() {
        $('body').append("<div class='ui-loader-background'> </div>");
        $.mobile.loading('show');
    }

   function hideModal() {
        $(".ui-loader-background").remove();
        $.mobile.loading('hide');
    }


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


	function onBackPressed(){
        
        if(pageStack.length <= 0)
        {
            if(currentPage == '#home')
            {
                navigator.app.exitApp();
            }
            else
            {
                var pageName = "#home";
                switchPage(pageName, false);
            }       
        }
        else
        {
            var pageName = pageStack.pop();
	        switchPage(pageName, false);
        }
    }

    function switchPage(nameOfPage, pushStack) 
    {
        if (nameOfPage != "") {
            $(":mobile-pagecontainer").pagecontainer("change", nameOfPage, {});
            //Updating Page Stack
            if(pushStack)
            {
                if(pageStack.length >= 5)
                    pageStack.shift();
                pageStack.push(currentPage);
            }
            currentPage = nameOfPage;
        }

        if(nameOfPage == '#fourm-topic')   
        {
            $( "#TopHeader" ).show();
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
            
        }
        else if(nameOfPage == '#fourm-storie') 
        {
            $( "#TopHeader" ).show();
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
            
        }
        else if(nameOfPage == '#survey') 
        {
            $( "#TopHeader" ).hide();
            if("userID" in localStorage)
            {
                $.post( SERVER + "/WebServices/checkSurvey.php",
                {
                    
                }, function (data, status)
                {
                    
                    var jsonObj = data;            
                    if (jsonObj.code == 2)
                    {
                        SurveyComplete = true;
                        currentPage = '#home';
                        switchPage('#home', false);       
                    }

                });
            }            
        }
        else if(nameOfPage == '#top-250') 
        {
            $( "#TopHeader" ).show();
            currentPage = '#top-250';
            $('#paginate250').html('');
            showModal();

            $('#CalculatedGamesList').html(' ');
            $('#Top250GamesHeading').html('Your Top 250 Games');
            var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
            $.post( SERVER + "/WebServices/getUserTopGames.php",
            {
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
        }
        else if(nameOfPage == '#movies-top-10')
        {
            $( "#TopHeader" ).show();
        /*    if("userID" in localStorage)
            {
                currentPage = "#home";
                
                //showModal();
                $('#mainTop10').html(' ');
                $('#TopGamesHeadingHome').html('Your Top 10 Games');
                var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
                $.post( SERVER + "/WebServices/getUserTopGames.php",
                {
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

                        $('#mainMovies').html(movie);
                        $('#mainMovies').listview('refresh');                   
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
        }
        else if(nameOfPage == '#home') 
        {
            $( "#TopHeader" ).show();
            if("userID" in localStorage )
            {
                currentPage = "#home";
                
                //showModal();
                var ID = localStorage.getItem("ID");
                $('#mainTop10').html(' ');
                $('#TopGamesHeadingHome').html('Your Hot 10 Games');
                var ul, li, a, img, span1, h2, p1, p2, div, span2, thermometer, div_img, div_det;
                $.post( SERVER + "/WebServices/getUserTopGames.php",
                {
                    userID:ID,
                    limit: 10
                }, function (data, status)
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
                                        $("#a"+ parseInt(j) + surveyData[i].id ).prop( "checked", true );
                                    }
                                }
                            }                                    
                        }
                        else if (jsonObj.code == 2)
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
                        $('#mainTop10').html(game);
                        $('#mainTop10').listview('refresh');

                        
                        firstTime = false;

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
        }

    }


    function pushCurrentPage(page)
    {
    	
        if(pageStack.length >= 5)
            pageStack.shift();
    	pageStack.push(page);
        
    }


    function refreshHome()
    {
        
        $( "#TopHeader" ).show();
        currentPage = "#home";
        
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
                $('#mainTop10').html(game);
                $('#mainTop10').listview('refresh');
                
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
    };