"use strict";

/** @jsx dom */
var dom = React.createElement;

ReactDOM.render(dom(
  "h1",
  null,
  "Hello, ",
  dom(
    "em",
    null,
    "world!"
  ),
  " ",
  dom(
    "button",
    { className: "btn btn-primary" },
    "OK"
  ),
  " ",
  dom(MyComponent, { options: "foo" })
), document.getElementById('main'));

//# sourceMappingURL=test.js.map
