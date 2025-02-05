! function (i) {
    var e = {
            delimiter: ",",
            divideThousand: !0,
            delimiterRegExp: /[\.\,\s^a-zA-Z]/g
        },
        n = i.fn.val;
    i.fn.val = function (i) {
        var e = this.data("divided");
        return "undefined" == typeof i ? e ? e.value : n.call(this) : e ? n.call(this, i)
            .change() : n.call(this, i)
    }, i.fn.divide = function (n) {
        function t(i) {
            return !i.is("input, textarea")
        }

        function a(i) {
            return !isNaN(i)
        }

        function d(i) {
            if (i = i.replace(options.delimiterRegExp, ""), !a(i) && i.length > 0) {
				console.warn(i + " is not a number"), -1;
				return -1;
			}
            for (var e = "", n = i.length; n > 3;) {
                if (4 == i.length) {
                    e = (options.divideThousand ? options.delimiter : "") + i.substring(1), n = 1;
                    break
                }
                n -= 3, e = options.delimiter + i.substring(n, n + 3) + e
            }
            return i.substring(0, n) + e
        }
        return options = i.extend({}, e, n), this.each(function () {
            var e = i(this);
            !t(e) && e.data("divided") && e.unbind(".divide")
        }), this.each(function () {
            var e = i(this);
            if (t(e)) {
                var n = d(e.text());
                return void(n != -1 && e.text(n))
            }
            e.bind("input.divide change.divide", function () {
                var i = this.value.replace(options.delimiterRegExp, ""),
                    n = d(i),
                    t = e.data("divided");
                if (n != -1) {
                    if (!t) {
                        t = {};
                        var a = e.attr("name");
                        void 0 != a ? (e.attr("name", ""), e.parent()
                            .append("<input type='hidden' name='" + a + "'>"), t.name = a) : t.name = null
                    }
                    t.value = i, this.value = n, e.data("divided", t), t.name && e.parent()
                        .children("input[name='" + t.name + "']")
                        .val(i)
                }
            }), e.change()
        }), this
    }
}(jQuery);