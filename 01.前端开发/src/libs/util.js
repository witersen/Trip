let util = {};

util.title = function (title) {
    title = title ? title + ' - Trip' : 'Trip';
    window.document.title = title;
};

export default util;
