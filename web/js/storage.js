function storeInLocalStorage(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

function storeInSessionStorage(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

function retrieveFromLocalStorage(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = localStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}

function retrieveFromSessionStorage(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = sessionStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}