(function($){
    var Target = {
        fetching: false,
        founding: false,

        start: function(injson) {
            this.blockData     = $.parseJSON(injson);
            this.curTime       = this.blockData.time;
            this.time();

            this.checkInterval = setInterval($.proxy(this.check, this), 5000);
            this.timeInterval  = setInterval($.proxy(this.time,  this), 1000);
            this.hashInterval  = setInterval($.proxy(this.hash,  this), 50);
        },
        check: function() {
            $.get('/height.json').done($.proxy(function(data) {
                this.curTime = data.time;
                if (this.blockData.blocks[0].height != data.height) {
                    clearInterval(this.checkInterval);
                    this.fetch().done($.proxy(this.found, this));
                }
            }, this));
        },
        time: function() {
            var t = this.curTime - this.blockData.blocks[0].time;
            this.curTime++;

            this.formatBlocks();
            $('.currtime').text(this.formatTime(t, true));
            if (this.founding) {
                document.title = "\u2713 Bitcoin Target";
            } else {
                document.title = "\u23F3 " + this.formatTime(t) + ': Bitcoin Target';
            }
        },
        fetch: function() {
            if (this.fetching) {
                return $.Deferred().reject();
            }

            this.fetching = true;
            var p = $.Deferred();
            $.get('/data.json', 'json').done($.proxy(function(data) {
                this.fetching = false;
                this.blockData = data;
                p.resolve();
            }, this));
            return p;
        },
        hash: function() {
            var str = '';
            for (var i=0; i<64; i++) {
                str += (0 | (Math.random() * 16)).toString(16);
            }
            $('.currhash').text(str);
        },
        found: function() {
            this.founding = true;
            clearInterval(this.hashInterval);
            var head = this.blockData.blocks[0];

            $('.target h2').text('Found block ' + head.height);
            $('.blocks li:nth-child(8)').remove();
            $('.blocks ul').prepend($(
                '<li><a href="http://blockchain.info/block-index/' + head.hash + '">' + head.height + '</a>, ' +
                '<span class="time" rel="' + head.time + '"></span></li>'
            ));
            $('.currhash').addClass('found').text(head.hash);
            $('.currtime').text(' ');

            setTimeout($.proxy(function() {
                this.founding = false;
                $('.target h2').text('Finding block ' + (head.height + 1) + ' at difficulty ' + (0 | this.blockData.difficulty));
                $('.currhash').removeClass('found');
                this.hashInterval  = setInterval($.proxy(this.hash, this), 50);
                this.checkInterval = setInterval($.proxy(this.check, this), 5000);
            }, this), 10000);
        },
        formatTime: function(n, sec) {
            sec = sec || false;
            return (0 | (n / 60)) + ' min' + (sec ? (' ' + (n % 60) + ' sec') : '');
        },
        formatBlocks: function() {
            $('.blocks li').each($.proxy(function(i, el) {
                var t = $(el).find('span.time');
                t.text(this.formatTime(this.curTime - t.attr('rel')));
            }, this));
        }
    };

    $(document).ready(function() {
        Target.start($('#initialdata').text());
    });
})(jQuery);
