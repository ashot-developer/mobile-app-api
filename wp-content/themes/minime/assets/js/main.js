jQuery(document).ready(function ($) {
    $('a[href="#chat-modal"]').on('click', function (e) {
        e.preventDefault();

        let dialog = '';
        const chatId = $(this).data('id');
        const modal = $($(this).data('modal'));
        const hostId = $(this).data('host');
        modal.find('.dot-spinner').css('display', 'flex');
        modal.find('.voldemort').css('display', 'none');
        $.ajax({
            url: 'https://http-chat-get-messages-7vxnir2s7q-uc.a.run.app',
            crossDomain: true,
            type: "get",
            data: {
                chatId: chatId
            },
            success: function (data) {
                let res = JSON.parse(data);
                res.forEach(m => {
                    var date = new Date(m.createdAt);
                    var formattedDate = date.toLocaleString('en-GB', { timeZone: 'UTC' });

                    dialog += `
                    <div class=" ${m.recipient === hostId ? 'incoming' : 'outgoing'} message-item">
                        <div class="bubble">
                        <span class="sent__by"> ${m.recipientName} </span>
                        <p>${m.text}</p>
                        <span>${formattedDate }</span>
                        </div>
                        
                    </div>
                `
                })
                modal.find('.voldemort').html(dialog);
                modal.find('.dot-spinner').css('display', 'none');
                modal.find('.voldemort').css('display', 'flex');
            },
            error: function (error) {
                console.log('Error loading data:', error);
            }
        });
        modal.modal();
        modal.find('a[href="#close-modal"]').text('');
        $(".voldemort").animate({ scrollTop: $('.voldemort').prop("scrollHeight") }, 500);
    })

    $('a[href="#passport-modal"]').on('click', function (e) {
        e.preventDefault();
        let img = $(this).find('img').attr('src');
        $($(this).data('modal')).find('img').attr('src', img);
        $($(this).data('modal')).modal();

        return false;
    });

    $('a[href="#terms-modal"]').on('click', function (e) {
        e.preventDefault()
        const modal = $($(this).data('modal'));
        const Uid = $(this).data('id');
        modal.find('.dot-spinner').css('display', 'flex');
        modal.find('.terms-res').css('display', 'none');
        $.ajax({
            url: 'https://http-tac-get-7vxnir2s7q-uc.a.run.app',
            crossDomain: true,
            type: "get",
            data: {
                userUid: Uid
            },
            success: function (data) {
                let res = JSON.parse(data).text;
                modal.find('.dot-spinner').css('display', 'none');
                modal.find('.terms-res').html(res);
                modal.find('.terms-res').css('display', 'block');
            },
            error: function (error) {
                console.log('Error loading data:', error);
            }
        });
        modal.modal();
        modal.find('a[href="#close-modal"]').text('');

        return false;
    });
});


