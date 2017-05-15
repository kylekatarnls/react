/** @jsx dom */
var dom = React.createElement;

ReactDOM.render(
    <h1>Hello, <em>world!</em> <button className="btn btn-primary">OK</button> <MyComponent options="foo" /></h1>,
    document.getElementById('main')
);
