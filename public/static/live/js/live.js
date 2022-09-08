/*
 * web socket
 */

var ws;//websocket实例
var lockReconnect = false;//避免重复连接
var wsUrl = 'ws://127.0.0.1:9501';

var clientName = null;

function initEventHandle() {
    ws.onclose = function (e) {
        console.log(e)
        //reconnect(wsUrl);
    };
    ws.onerror = function (e) {
        console.log(e)
        //reconnect(wsUrl);
    };
    ws.onopen = function () {
        //心跳检测重置
        heartCheck.reset().start();
    };
    ws.onmessage = function (event) {
        //如果获取到消息，心跳检测重置
        //拿到任何消息都说明当前连接是正常的
        console.log("ws data: ",event.data)
        let data = JSON.parse(event.data);
        if (data.hasOwnProperty('status') && data.status === 1) {
            createTpl(data)
        }
        if (data.hasOwnProperty('client_name') && data.status === 0) {
            clientName = data.client_name
        }
        heartCheck.reset().start();
    }
}


function createTpl(data) {
    if (data.hasOwnProperty('type') && parseInt(data.type) === 1) {
        let html = `<div class="frame">
                <h3 class="frame-header">
                    <i class="icon iconfont icon-shijian"></i>第一节 01：30
                </h3>
                <div class="frame-item">
                    <span class="frame-dot"></span>
                    <div class="frame-item-author">
                        <img src="./imgs/team1.png" width="20px" height="20px"/> 马刺
                    </div>
                    <p>08:44 ${data.content}</p>
                </div>
            </div>`;

        $('#match-result').append(html);
        return false;
    }

    if (data.hasOwnProperty('type') && parseInt(data.type) === 2) {
        let html = `<div class="comment">
                    <span>xixi</span>
                    <span>${data.content}</span>
                </div>`;

        $('#comments').append(html);
        return false;
    }
}

createWebSocket(wsUrl);

/**
 * 创建链接
 * @param url
 */
function createWebSocket(url) {
    try {
        ws = new WebSocket(url);
        initEventHandle();
    } catch (e) {
        console.log(e);
        //reconnect(url);
    }
}

function reconnect(url) {
    if (lockReconnect) return;
    lockReconnect = true;
    //没连接上会一直重连，设置延迟避免请求过多
    setTimeout(function () {
        createWebSocket(url);
        lockReconnect = false;
    }, 2000);
}

//心跳检测
var heartCheck = {
    timeout: 60000,//60秒
    timeoutObj: null, serverTimeoutObj: null, reset: function () {
        clearTimeout(this.timeoutObj);
        clearTimeout(this.serverTimeoutObj);
        return this;
    }, start: function () {
        var self = this;
        this.timeoutObj = setTimeout(function () {
            //这里发送一个心跳，后端收到后，返回一个心跳消息，
            //onmessage拿到返回的心跳就说明连接正常
            ws.send("heartbeat");
            self.serverTimeoutObj = setTimeout(function () {//如果超过一定时间还没重置，说明后端主动断开了
                ws.close();//如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
            }, self.timeout);
        }, this.timeout);
    }, header: function (url) {
        window.location.href = url
    }

}