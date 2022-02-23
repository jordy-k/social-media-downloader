// function
let playButton = () => {
    let top = ($('.vjs-poster').height() - $('.vjs-big-play-button').height()) / 2;
    let left = ($('.vjs-poster').width() - $('.vjs-big-play-button').width()) / 2;
    $('.vjs-big-play-button').css('top', top);
    $('.vjs-big-play-button').css('left', left);
};

let processData = () => {
    var fd = new FormData();
    fd.append('url', $('#link').val());
    $.ajax({
        url: location.href + 'scraper/',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: (data) => {
            let mediaUrl = `${location.href}preview/?type=video&url=${encodeURIComponent(data.source)}&filename=${encodeURIComponent(data.filename)}.mp4`;
            let posterUrl = `${location.href}preview/?type=image&url=${encodeURIComponent(data.poster)}&filename=${encodeURIComponent(data.filename)}`;
            console.log(mediaUrl);
            console.log(posterUrl);
            vjs.src(mediaUrl);
            vjs.poster(posterUrl);
            $('#filename-generated').html(data.filename);
            $('#download').attr('href', mediaUrl);
            setTimeout(() => {
                // $('#video-generated_html5_api').attr('src', mediaUrl);
            }, 500);
        },
        error: (err) => {
            reject(err);
        }
    });
};

// Initiation
let vjs = videojs('#video-generated', {
    muted: true,
});
let buttonInterval = setInterval(() => {
    playButton();
}, 1000);

$('#generate').click(() => {
    processData();
});
$('#link').change(() => {
    if ($('#link').val().length > 0) {
        processData();
    }
});