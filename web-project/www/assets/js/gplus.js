function shareToGooglePlus() {
    sharelink = "https://plus.google.com/share?url=" + window.location.href;
    newwindow = window.open(sharelink, 'name', 'height=400,width=600');
    if (window.focus) {
        newwindow.focus()
    }
    return false;
}