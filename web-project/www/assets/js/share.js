function shareLink(title) {
    bootbox.prompt({
        title: title,
        value: window.location.href,
        callback: function (result) {
            if (result !== null) {
                // copyToClipboard($('input.bootbox-input-text')); TODO
            }
        }
    });
}