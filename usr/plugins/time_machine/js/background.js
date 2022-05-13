chrome.contextMenus.create({
    title: "发送“%s”到时光机",
    contexts: ['selection'],
    onclick: function(info, tab){
        //设定打开页面的一些初始值
        console.log(info);
        chrome.storage.sync.set({open_action: "save_text",open_content:info.selectionText}, function() {
            chrome.windows.create({
                url: chrome.extension.getURL("html/popup.html"),
                left: 50,
                top: 50,
                width: 420,
                height: 200,
                type: "popup"
            });
        });
    }
});

chrome.contextMenus.create({
    title: "发送这张图片到时光机",
    contexts: ['image'],
    onclick: function(info, tab){
        console.log(info);
        //设定打开页面的一些初始值
        chrome.storage.sync.set({open_action: "upload_image",open_content:info.srcUrl}, function() {
            chrome.windows.create({
                url: chrome.extension.getURL("html/popup.html"),
                left: 50,
                top: 50,
                width: 420,
                height: 200,
                type: "popup"
            });
        });
    }
});


