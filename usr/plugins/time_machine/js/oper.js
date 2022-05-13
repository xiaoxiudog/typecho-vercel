// 读取数据，第一个参数是指定要读取的key以及设置默认值


/**
 * open_action: 打开这个页面执行的操作
 * open_text：打开这页面需要复原的输入框的内容
 */
get_info(function (info) {
    if (info.status){//已经有绑定信息了，折叠
        $("#blog_info").hide();
    }
    $("#identify").val(info.key);
    $("#url").val(info.url);
    $("#cid").val(info.cid);
    if (info.open_action === "upload_image"){//打开的时候就是上传图片
        console.log(info.open_content);
        uploadImage(".jpg",info.open_content);
    }else {
        $("#content").val(info.open_content);
    }
    //从localstorage 里面读取数据
    setTimeout(get_info,1)
});


//监听输入结束，保存未发送内容到本地
$("#content").blur(function(){
    chrome.storage.sync.set({open_action: "save_text",open_content:$("#content").val()}, function() {});
});


//监听拖拽事件，实现拖拽到窗口上传图片
initDrag();

//监听复制粘贴事件，实现粘贴上传图片
document.addEventListener('paste', function (event) {
    var cbd = event.clipboardData;
    var items = event.clipboardData && event.clipboardData.items;
    var file = null;
    var ua = window.navigator.userAgent;
    var fileName = null;
    if (items && items.length) {
        // 检索剪切板items
        for (var i = 0; i < items.length; i++) {
            // console.log(items[i]);
            if (items[i].type.indexOf('image') !== -1) {
                file = items[i].getAsFile();
                if (i-1 >=0){
                    fileName = items[i-1];
                }
                break;
            }
        }
    }
    var obj = this;
    //file是一个blob对象
    //通过复制获取的图片file中不是真实的文件名称，文件名称是上一个item中
    if (file!=null){
        obj.value="";
        //阻止默认的复制
        event.preventDefault();
        event.stopPropagation();
        if (fileName!=null){
            fileName.getAsString(function (str) {
                // console.log("string" + str);//图片的名称
                //通过图片名称获取文件后缀
                var index = str.lastIndexOf(".")+1;
                var type = "jpg";
                if (index !== -1){
                    type = str.substr(index);
                }else{
                    type = "jpg";
                }
                updateImageByFile(str,"."+type,"image/"+type,file,function () {
                });
            });
        }else{
            console.log(file.type);
            var type = file.type.substr(file.type.lastIndexOf("/")+1);
            updateImageByFile(file.name,"."+type,"image/"+type,file,function () {
                //阻止默认的复制
                event.preventDefault();
                event.stopPropagation();
                obj.value="";
            });
        }
    }else{//不是图片

    }

});


function updateImageByFile(name,type,format,file,callback) {
    //file是一个blob对象
    console.log(type);
    if (file!=null){
        //对图片进行质量压缩
        changeBlobImageQuality(file,function (data) {
            uploadImage(type,data);
        },format);
    }else {
        $.message({
            message:"复制类型"+type,
        });
    }

    if (callback) {
        callback();
    }
}

function initDrag()
{
    var file = null;
    var obj = $('#content')[0];
    obj.ondragenter = function(ev) {
        if (ev.target.className === "textarea"){
            console.log("ondragenter" + ev.target.tagName );
            $.message({
                message:"拖拽到窗口上传该图片",
                autoClose: false
            });
            $('body').css("opacity",0.3);
        }

        ev.dataTransfer.dropEffect = 'copy';
    };
    obj.ondragover = function(ev) {
        console.log("ondragover");

        ev.preventDefault(); //防止默认事件拖入图片 放开的时候打开图片了
        ev.dataTransfer.dropEffect = 'copy';
    };
    obj.ondrop = function(ev) {
        console.log("ondrop");
        $('body').css("opacity",1);
        ev.preventDefault();
        var files =  ev.dataTransfer.files || ev.target.files;
        for(var i=0; i<files.length; i++) {
            if(files[i].type.indexOf('image') >= 0) {
                file = files[i];
                break;
            }
        }
        // console.log(file);
        var type = file.type.substr(file.type.lastIndexOf("/")+1);
        updateImageByFile(file.name,"."+type,"image/"+type,file);
    };
    obj.ondragleave = function (ev) {
        ev.preventDefault();
        if (ev.target.className === "textarea"){
            console.log("ondragleave" + ev.target.tagName );
            $.message({
                message:"取消上传",
            });
            $('body').css("opacity",1);
        }
    }
}



function uploadImage(type,data) {

    //显示上传中的动画……
    $.message({
        message:"上传图片中……",
        autoClose: false
    });
    //根据data判断是图片地址还是base64加密的数据
    get_info(function (info) {
        if (info.status){
            checkIsRecordUser(info,function (flag) {
                if (flag){
                    $.post(
                        info.url,
                        {
                            action: "upload_img",
                            time_code: $.md5(info.key),
                            token:"crx",
                            file: data,
                            mediaId: "1",
                            type: type
                        }, function(result){
                            console.log(result);
                            var object = JSON.parse(result);
                            if (object.status === "1") { //获取到图片
                                chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                                    $.message({
                                        message:"上传成功"
                                    });
                                    $("#content").val($("#content").val()+"<img src='"+object.data+"' />");
                                });
                            } else { //发送失败
                                //清空open_action（打开时候进行的操作）,同时清空open_content
                                chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                                    if (object.status === "-3"){
                                        $.message({
                                            message:"身份编码错误，请仔细检查绑定格式和内容"
                                        });
                                    }else {
                                        $.message({
                                            message:"上传图片失败,错误码" + result
                                        })
                                    }
                                });
                            }
                        }).fail(function() {
                        //清空open_action（打开时候进行的操作）,同时清空open_content
                        chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                            $.message({
                                message:"网络问题上传失败"
                            });
                        });
                    });
                }else {
                    $.message({
                        message:"盗版用户，无法使用主题任何功能！"
                    })
                }
            });
        }else {
            $.message({
                message:"所需要信息不足，请先填写好绑定信息"
            })
        }
    });

}

$("#saveKey").click(function () {
    // 保存数据
    chrome.storage.sync.set({key: $("#identify").val(),url:$("#url").val(),cid: $("#cid").val()}, function() {
        $.message({
            message:"保存时光机验证编码成功"
        });
    });
});

$("#blog_info_edit").click(function () {
   $("#blog_info").slideToggle();
});


function get_info(callback) {
    chrome.storage.sync.get({key: "",url:"",cid:"",open_action:"",open_content:""}, function(items) {
        var flag = false;
        var returnObject = {};
        if (items.key === "" || items.url === ""|| items.cid ===""){
            flag = false;
        }else {
            flag = true;
        }
        returnObject.status = flag;
        returnObject.key = items.key;
        returnObject.url = items.url;
        returnObject.cid = items.cid;
        returnObject.open_content = items.open_content;
        returnObject.open_action = items.open_action;
        if (callback) callback(returnObject);
    });
}

//发送操作
$("#submit").click(function () {
    sendText();
});

function sendText() {
    get_info(function (info) {
        if (info.status){//信息满足了
            checkIsRecordUser(info,function (flag) {
                if (flag){
                    $("#content_submit_text").text("发送中……");
                    //支持私密评论
                    let content = $("#content").val();
                    var first = content.substr(0,1);
                    if (first === "#"){
                        content = "[secret]" + content.substr(1) + "[/secret]";
                    }
                    $.post(
                        info.url,
                        {
                            action: "send_talk",
                            time_code: $.md5(info.key),
                            cid: info.cid,
                            token:"crx",
                            content: content,
                            msg_type: "text",
                            mediaId: "1"
                        }, function(result){
                            $("#content_submit_text").text("发表新鲜事");
                            if (result === "1") { //发送成功
                                chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                                    $.message({
                                        message:"biubiubiu~发送成功"
                                    });
                                    $("#content").val("");
                                });
                            } else { //发送失败
                                //清空open_action（打开时候进行的操作）,但不清空open_content
                                chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                                    if (result === "-3"){
                                        $.message({
                                            message:"身份编码错误，请仔细检查绑定格式和内容"
                                        });
                                    }else {
                                        $.message({
                                            message:"发送失败,错误码" + result
                                        })
                                    }
                                });
                            }
                        }).fail(function() {
                        //清空open_action（打开时候进行的操作）,同时清空open_content
                        chrome.storage.sync.set({open_action: "",open_content:""}, function() {
                            $.message({
                                message:"网络问题上传失败"
                            });
                        });
                    });
                }else {
                    $.message({
                        message:"盗版用户，无法使用主题任何功能！"
                    })
                }
            });
        }else {
            $.message({
                message:"所需要信息不足，请先填写好绑定信息"
            })
        }
    });
}






/**
 * 改变blob图片的质量，为考虑兼容性
 * @param blob 图片对象
 * @param callback 转换成功回调，接收一个新的blob对象作为参数
 * @param format 目标格式，mime格式
 * @param quality 介于0-1之间的数字，用于控制输出图片质量，仅当格式为jpg和webp时才支持质量，png时quality参数无效
 */
function changeBlobImageQuality(blob, callback, format, quality)
{
    format = format || 'image/jpeg';
    quality = quality || 0.9; // 经测试0.9最合适
    var fr = new FileReader();
    fr.onload = function(e)
    {
        var dataURL = e.target.result;
        console.log(dataURL);
        if(callback) callback(dataURL);
        /*var img = new Image();
        img.onload = function()
        {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            var newDataURL = canvas.toDataURL(format, quality);//base64
            console.log(newDataURL);
            if(callback) callback(newDataURL);
            canvas = null;
        };
        img.src = dataURL;*/
    };
    fr.readAsDataURL(blob); // blob 转 dataURL
}


function checkIsRecordUser(info,callback) {
    $("#content_submit_text").text("发送中……");
    $.ajax({
        type:'GET',
        url: "https://auth.ihewro.com/user/notice2?token=crx&url="+info.url,
        error: function (data) {
            $("#content_submit_text").text("发表新鲜事");
            $.message({
                message:"请求失败"
            })
        },
        success: function (data) {
            $("#content_submit_text").text("发表新鲜事");
            if (data.action === "1"){//盗版用户
                callback(false);
            }else{
                callback(true);
            }
        }
    })


}