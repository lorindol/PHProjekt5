// chat.js - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther ag@phprojekt.com
// www.phprojekt.com
// Authors: Albrecht Guenther, Uwe Pries
// $Id: chat.js,v 1.4 2006/10/14 01:48:19 gustavo Exp $

function CHAT(input, output, dir) {
    this.text = '';
    this.dir  = (typeof this.dir != 'undefined' ? 'top' : dir);

    this.input = input;
    this.output = output;

    this.input.focus();

    if (this.dir == 'bottom') {
        var me = this;
        window.setTimeout(function() {
            me.output.scrollTop = 10000;
        }, 500);
    }
};

CHAT.prototype.send = function(text, cb) {
    var me = this;
    dojo.io.bind( {
            url: 'chat.php?supress=1&' + text,
            handler: function(type, data, evt){
                    if (cb == 'output') {
                        me.set_output(data);
                    } else {
                        document.getElementById('chatUsers').innerHTML = data;
                    }
            },
            mimetype: "text/plain"}
    );
};

CHAT.prototype.list = function() {
    this.send('mode=list', 'output');
};

CHAT.prototype.alive = function() {
    this.send('mode=alive', 'alive');
}

CHAT.prototype.set_output = function(text, dir) {
    // return on same text
    if (this.text == text || text == '') {
        return;
    }

    var at_botton = false;
    if (this.output.scrollTop + this.output.clientHeight == this.output.scrollHeight) {
        at_botton = true;
    }

    // save it
    this.text = text;
    this.output.innerHTML = text; 

    // move over only on dir == bottom
    if (this.dir != 'bottom') {
        return;
    }

    if (!at_botton) {
        return;
    }

    // due to timing...
    var me = this;
    window.setTimeout(function() {
        me.output.scrollTop = 10000;
    }, 10);
};

CHAT.prototype.send_input = function(input) {
    if (typeof input != 'undefined' && input != null) {
        this.input.value = input;
    }

    if (this.input.value == '') {
        return;
    }

    var content = escape(this.input.value).replace(/\+/g, '%2B').replace(/\"/g,'%22').replace(/\'/g, '%27');
    this.send("mode=write&content=" + content, 'output');
    this.input.value = '';
    this.input.focus();
};

CHAT.prototype.onclick = function(e) {
    switch (this.get_target(e, 'id')) {
    case 'send':
        this.send_input();
        break;
    }
};

CHAT.prototype.onkeyup = function(e) {
    var id = this.get_target(e, 'id');
    var evt = e || event;

    switch (id) {
    case 'content':
        if (evt.keyCode == 13) {
            this.send_input();
        }
        break;
    }
};

CHAT.prototype.get_target = function(event, attr) {
    var target = (typeof event.target != "undefined" ? event.target : event.srcElement);

    if (typeof attr == "undefined") {
        return target;    
    }

    return target[attr];
};

CHAT.prototype.insert_firstname = function(name) {
    name = name.split(" ");

    this.input.value = name[0] + ": " + this.input.value;
    this.input.focus();
}


CHAT.prototype.insert_msgname = function(name) {
    this.input.value = "/msg " + name + " " + this.input.value;
    this.input.focus();
}

CHAT.prototype.insert_smiley = function(name) {
    this.input.value = this.input.value + (this.input.value != '' ? ' ' : '') + name;
    this.input.focus();
}

CHAT.prototype.insert_think = function(name) {
    this.input.value = ". o O ( " + this.input.value +" )";
    this.input.focus();
}
