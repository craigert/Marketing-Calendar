( function( $ ) {

	$( document ).ready( function() {

		// Prepend event start time to event title on calendar
		$("li.simcal-event").each(function( index ) {
			$(this).find("span.simcal-event-start-time").css("font-weight","bold").prependTo($(this));
		});

		// Remove erroneous <br> tags added by plugin
		$("div.calendars").find('br').remove();

		// Add 2 spaces after start time to give more space
		$("span.simcal-event-start-time").append("<span>&nbsp;-&nbsp;</span>");

		// Style event title and background to match Google Calendar
		$("span.simcal-event-title").each(function( index ) {
			var eventColor = $(this).children().css("color");
			$(this).css("background", eventColor);
			$(this).parent().css("color", "white");
			$(this).find("span").css("display", "none");
		});

		$('div.calendarFrame iframe').load(function(){
			var calendarFrameHeight = $(window).height();
			var calendarFrameHalfHeight = (calendarFrameHeight / 2) - 80;

			var $c = $('div.calendarFrame iframe').contents();

			$c.find("td.simcal-day div:first-child").addClass("dayheight");

			var calendarFrameHalfHeightStyle = calendarFrameHalfHeight+"px";

			$c.find("div.dayheight").css("min-height", calendarFrameHalfHeightStyle);

			var currentRowPos = $c.find('td.simcal-present').parent().offset().top; //get the offset top of the current row
			var currentRowScroll = currentRowPos - $(window).scrollTop(); //position of the ele w.r.t window

			$(this).css('margin-top', Math.abs(currentRowScroll) * -1);
			var iframeHeight = $(this).contents().find("html").height();
			$(this).css('height', iframeHeight);

			$c.find("div.dayheight").css("min-height", calendarFrameHalfHeightStyle);
		});

		// Array of all events
		var eventTitleArray = $("li.simcal-event > span.simcal-event-title").map(function() {
			return this.innerHTML;
		}).get();

		var i;

	//	for (i = 0; i < eventTitleArray.length; ++i) {
	//		// do something with `substr[i]`
	//	}
	//
	//	$('body').append('<pre>'+JSON.stringify(eventTitleArray,  null, ' '))

	} );
} )( jQuery );
