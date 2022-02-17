// function
let playButton = () => {
    let top = ($('.vjs-poster').height() - $('.vjs-big-play-button').height()) / 2;
    let left = ($('.vjs-poster').width() - $('.vjs-big-play-button').width()) / 2;
    $('.vjs-big-play-button').css('top', top);
    $('.vjs-big-play-button').css('left', left);
}

let processData = () => {
    var fd = new FormData();
    fd.append('url', $('#link').val());
    $.ajax({
        url: location.href + '/scraper/',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: (data) => {
            vjs.src('https://www.w3schools.com/html/movie.mp4');
            vjs.poster(data.poster);
            $('#filename-generated').html(data.filename);
            setTimeout(() => {
                $('#video-generated_html5_api').attr('src', data.source);
            }, 500);
        },
        error: (err) => {
            reject(err);
        }
    });
}

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
$('#link').keyup(() => {
    processData();
});