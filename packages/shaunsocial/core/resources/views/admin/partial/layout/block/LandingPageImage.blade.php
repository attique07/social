<input id="photo_id" name="photo_id" type="hidden" value="">
<div class="form-group">
    <label class="control-label">{{__('Image')}}</label>
    <input id="image" class="form-control" name="image" type="file">
    <div id="content_image" style="display: none;padding-top:5px;">
        <img id="image" width="200px" />
    </div>
</div>

<script>
    function getLandingPageImage()
    {
        return {
            'photo_id': $('#modal-ajax #photo_id').val()
        }
    }
    function setLandingPageImage(data)
    {
        photo_id = '';
        if (typeof data.photo_id !== "undefined") {
            photo_id = data.photo_id;
        }

        $.ajax({ 
            type: 'POST', 
            url: '{{route('admin.layout.get_data_block')}}', 
            data: {'photo_id' : photo_id, 'component': 'LandingPageImage'}, 
            dataType: 'json',
            success: function (data) {
                if (data.status){
                    $('#modal-ajax #content_image').show()
                    $('#modal-ajax #image').attr('src', data.data.photo_url)
                }
            }
        });

        $('#modal-ajax #image').change(function(){
            const formData = new FormData();

            formData.append('file',$(this)[0].files[0]);

            $.ajax({ 
                type: 'POST', 
                url: '{{route('admin.layout.upload_file_block')}}', 
                data: formData, 
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status){
                        $('#modal-ajax #photo_id').val(data.data.file_id)
                        $('#modal-ajax #content_image').show()
                        $('#modal-ajax #image').attr('src', data.data.file_url)
                    }        
                }
            });
        });
        
        $('#modal-ajax #photo_id').val(photo_id)
        
    }
</script>