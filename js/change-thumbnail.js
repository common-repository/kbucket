(function($){
    $(document).ready(function () {
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            var parent
            var id
            var $media = wp.media({
                multiple: false
            });

            $media.on('select', function() {
                var attachment = $media.state().get('selection').first().toJSON();
                $.ajax({
                    url: my_ajax_object.ajax_url+'?action=save_kbucket_image',
                    type: 'POST',
                    data: {
                        url: attachment.url,
                        id_kbucket : id,
                        attach_id : attachment.id
                    },
                    success: function (data) {
                        imgDiv = $(parent).find('.image_wrap');
                        if(!imgDiv.length){
                            $(parent).find('.__img-source').attr('src', data.url)
                        }else{
                            imgDiv.css('background-image', 'url('+attachment.url.toString()+')')
                            imgDiv.removeClass('no_image')
                        }

                    }
                })
            })

            $('.open-gallery').click(function () {
                parent = $(this).parent()
                id = $(this).data('id')
                $media.open()
                return null;
            })
        }
    })
})(jQuery)

