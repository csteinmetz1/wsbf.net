$(document).ready(function() {
  formatGoogleCalendar.init({
    calendarUrl: "https://www.googleapis.com/calendar/v3/calendars/2lg875akr41qidf89age4ns3dk@group.calendar.google.com/events?key=AIzaSyD0xcZ0vCz7hKPqZX9TnhVXrvMTBEIGNo4",
    past: !1,
    upcoming: !0,
    sameDayTimes: !0,
    dayNames: !0,
    pastTopN: -1,
    upcomingTopN: 5,
    recurringEvents: !0,
    itemsTagName: "li",
    upcomingSelector: "#events-upcoming",
    pastSelector: "#events-past",
    upcomingHeading: "",
    pastHeading: "<h3>Past events</h3>",
    format: ["*summary*", ": <br>", "*date*", " <br>@ ", "*location*"],
    timeMin: "2016-06-03T10:00:00-07:00",
    timeMax: "2020-06-03T10:00:00-07:00"
  })
});
