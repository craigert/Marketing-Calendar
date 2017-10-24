(function($) {

    $(window).load(function() {

        // Prepend event start time to event title on calendar
        $("li.simcal-event").each(function(index) {
            $(this).find("span.simcal-event-start-time").css("font-weight", "bold").prependTo($(this));
        });

        // Remove miscellaneous <br> tags added by plugin
        $("div.calendars").find('br').remove();

        // Add 2 spaces after start time to give more space
        $("span.simcal-event-start-time").append("<span>&nbsp;-&nbsp;</span>");

        // Style event title and background to match Google Calendar
        $("span.simcal-event-title").each(function(index) {
            var eventColor = $(this).children().css("color");
            $(this).css("background", eventColor);
            $(this).parent().css("color", "white");
            $(this).find("span").css("display", "none");
        });

		// Get half height of calendar frame before display of calendar in order to render 2 weeks later
        // var calendarFrameHeight = $("div.calendarFrame").height();
        // var calendarFrameHalfHeight = calendarFrameHeight / 2;

        $("#globalCalendar")
			.delay(5000)
            .queue(function(next) {
                $(this).css("height", "800px");
                next();
        });

		// Split the calendar view in half to display 2 weeks at a time
        // $("td.simcal-day div:first-child").css("min-height", calendarFrameHalfHeight+"!important");

        // Add css class to each week specifying which number of week it is (for current week)
        $("#fullcalendar-182 .fc-view-container .fc-week").each(function(index) {
            if (index == "0") {
                $(this).addClass("firstWeek");
            } else if (index == "1") {
                $(this).addClass("secondWeek");
            } else if (index == "2") {
                $(this).addClass("thirdWeek");
            } else if (index == "3") {
                $(this).addClass("fourthWeek");
            } else if (index == "4") {
                $(this).addClass("fifthWeek");
            } else if (index == "5") {
                $(this).addClass("sixthWeek");
            }
        });
		
		// Add css class to each week specifying which number of week it is (for following week)
        $("#fullcalendar-236 .fc-view-container .fc-week").each(function(index) {
            if (index == "0") {
                $(this).addClass("first2Week");
            } else if (index == "1") {
                $(this).addClass("second2Week");
            } else if (index == "2") {
                $(this).addClass("third2Week");
            } else if (index == "3") {
                $(this).addClass("fourth2Week");
            } else if (index == "4") {
                $(this).addClass("fifth2Week");
            } else if (index == "5") {
                $(this).addClass("sixth2Week");
            }
        });

        // Funtion to determine which week of the month the current date is in
        Date.prototype.getMonthWeek = function() {
            var firstDay = new Date(this.getFullYear(), this.getMonth(), 1).getDay();
            return Math.ceil((this.getDate() + firstDay) / 7);
        }

		// Array of month names
        var monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        // Get current date
        var currentDate = new Date();

        // Set week number based on current date
        var weekNumber = currentDate.getMonthWeek();

        // Set month title to current month
        var month = monthNames[currentDate.getMonth()];

        // Get current year
        var year = currentDate.getFullYear();

		// Set month and year text on page
        $("span.month").html(month);
        $("span.year").html(year);

        // Hide any weeks before and after current week
		if (weekNumber == 1) {
			$(".firstWeek").show();
			$(".secondWeek").hide();
			$(".thirdWeek").hide();
			$(".fourthWeek").hide();
			$(".fifthWeek").hide();
			$(".sixthWeek").hide();
		} else if (weekNumber == 2) {
			$(".firstWeek").hide();
			$(".secondWeek").show();
			$(".thirdWeek").hide();
			$(".fourthWeek").hide();
			$(".fifthWeek").hide();
			$(".sixthWeek").hide();
		} else if (weekNumber == 3) {
			$(".firstWeek").hide();
			$(".secondWeek").hide();
			$(".thirdWeek").show();
			$(".fourthWeek").hide();
			$(".fifthWeek").hide();
			$(".sixthWeek").hide();
		} else if (weekNumber == 4) {
			$(".firstWeek").hide();
			$(".secondWeek").hide();
			$(".thirdWeek").hide();
			$(".fourthWeek").show();
			$(".fifthWeek").hide();
			$(".sixthWeek").hide();
		} else if (weekNumber == 5) {
			$(".firstWeek").hide();
			$(".secondWeek").hide();
			$(".thirdWeek").hide();
			$(".fourthWeek").hide();
			$(".fifthWeek").show();
			$(".sixthWeek").hide();
		} else if (weekNumber == 6) {
			$(".firstWeek").hide();
			$(".secondWeek").hide();
			$(".thirdWeek").hide();
			$(".fourthWeek").hide();
			$(".fifthWeek").hide();
			$(".sixthWeek").show();
		}
		
		// Hide any weeks before and after next week
		if (weekNumber == 1) {
			$(".first2Week").hide();
			$(".second2Week").show();
			$(".third2Week").hide();
			$(".fourth2Week").hide();
			$(".fifth2Week").hide();
			$(".sixth2Week").hide();
		} else if (weekNumber == 2) {
			$(".first2Week").hide();
			$(".second2Week").hide();
			$(".third2Week").show();
			$(".fourth2Week").hide();
			$(".fifth2Week").hide();
			$(".sixth2Week").hide();
		} else if (weekNumber == 3) {
			$(".first2Week").hide();
			$(".second2Week").hide();
			$(".third2Week").hide();
			$(".fourth2Week").show();
			$(".fifth2Week").hide();
			$(".sixth2Week").hide();
		} else if (weekNumber == 4) {
			$(".first2Week").hide();
			$(".second2Week").hide();
			$(".third2Week").hide();
			$(".fourth2Week").hide();
			$(".fifth2Week").show();
			$(".sixth2Week").hide();
		} else if (weekNumber == 5) {
			$(".first2Week").hide();
			$(".second2Week").hide();
			$(".third2Week").hide();
			$(".fourth2Week").hide();
			$(".fifth2Week").hide();
			$(".sixth2Week").show();
		} else if (weekNumber == 6) {
			$(".first2Week").show();
			$(".second2Week").hide();
			$(".third2Week").hide();
			$(".fourth2Week").hide();
			$(".fifth2Week").hide();
			$(".sixth2Week").hide();
		}

        // Set height of rows to 100% height to fill screen
        $(".simcal-calendar")
            .delay(1800)
            .queue(function(next) {
                $(".firstWeek").css('height', '100%');
                $(".secondWeek").css('height', '100%');
                $(".thirdWeek").css('height', '100%');
                $(".fourthWeek").css('height', '100%');
                $(".fifthWeek").css('height', '100%');
                $(".sixthWeek").css('height', '100%');
				$(".first2Week").css('height', '100%');
                $(".second2Week").css('height', '100%');
                $(".third2Week").css('height', '100%');
                $(".fourth2Week").css('height', '100%');
                $(".fifth2Week").css('height', '100%');
                $(".sixth2Week").css('height', '100%');
                next();
            });

        // Fix unicode characters inside event titles
        setTimeout(function() {
            $(".fc-content .fc-title").each(function(index) {
                var eventTitle = $(this).text();
                if (eventTitle.indexOf("&#039;") >= 0) {
                    eventTitle.replace("&#039;", "'");
                    $(this).html(eventTitle);
                } else {}
            });
			$(".fc-scroller .fc-day-grid-container").each(function(index) {
				$(this).css('overflow-x','hidden !important');
				$(this).css('overflow-y','hidden !important');
			});
        }, 400);

        /* TESTING - these aren't the droids you are looking for */

        //$('div.fc-content-skeleton tbody tr:first-child td:first-child').css('display', 'none');
        //$('div.fc-content-skeleton tbody tr:first-child td:last-child').css('display', 'none');

        // Array of all events
        //var eventTitleArray = $("li.simcal-event > span.simcal-event-title").map(function() {
        //	return this.innerHTML;
        //}).get();

        //var i;

        //	for (i = 0; i < eventTitleArray.length; ++i) {
        //		// do something with `substr[i]`
        //	}
        //
        //	$('body').append('<pre>'+JSON.stringify(eventTitleArray,  null, ' '))

        //	$("#globalCalendar").load(function () {
        //		frames["globalCalendar"].contentWindow.location.reload(true);
        //	});

    });
})(jQuery);