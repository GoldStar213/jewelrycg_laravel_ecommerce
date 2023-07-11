<form action="{{ route('seller.services.store') }}" method="post" enctype="multipart/form-data" onsubmit="submitForm(event)">
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            @csrf
            <div class="card col-md-12 mb-4">
                <div class="card-body">
                    <input type="hidden" name="step" id="name" value="{{ $step }}" class="form-control">
                    <input type="hidden" name="service_id" id="service_id" value="{{ $post_id }}">
                    @include('includes.validation-form')
                    <div class="mb-4">
                        <label for="name" class="w-100 mb-2 fw-700">Service title</label>
                        <input type="text" name="name" id="name" value="{{ null !== old('name') ? old('name') : (isset($data->name) ? $data->name : '') }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label for="category" class="w-100 mb-2 fw-700">Category</label>
                        <p>Select the appropriate category for your service.</p>
                        <div class="col-4">
                            <select class="selectpicker form-control" name="categories[]" data-live-search="true" data-container="body">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-tokens="{{ $category->category_name }}" {{ isset($data->categories) ? (count($data->categories) ? ($data->categories[0]->id_category === $category->id ? 'selected' : ''): '') : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="desc" class="w-100 mb-2 fw-700">Description</label>
                        <p>Provide a detailed description of your service.</p>
                        <div id="toolbar" class="cgeditor-toolbar">
                            <button type="button" data-command="bold" onclick="formatText('bold')"><i class="bi bi-type-bold"></i></button>
                            <button type="button" data-command="italic" onclick="formatText('italic')"><i class="bi bi-type-italic"></i></button>
                            <button type="button" data-command="bullets" onclick="formatText('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                        </div>
                        <div id="editor" class="border p-3" contenteditable="true" oninput="updateContent()" onpaste="handlePaste(event)">
                            {!! null !== old('content') ? old('content') : (isset($data->content) ? $data->content : '') !!}
                        </div>
                        <input type="hidden" name="content" id="content">

                    </div>
                    <div class="mb-4">
                        <label for="name" class="w-100 mb-2 fw-700">Tags</label>
                        <p>Include relevant keywords in the tags for your Service to help it be more easily discovered by potential customers.</p>
                        <select name="tags[]" id="tags" class="form-control select2" multiple="multiple" style="width: 100%;">
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" {{ isset($data->tag_ids) ? (count($data->tag_ids) ? (in_array($tag->id, $data->tag_ids) ? 'selected' : ''): '') : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-6 col-lg-8 col-md-8 mx-auto">
            <div class="row justify-content-center justify-content-sm-between">
                <div class="col">
                    <a class="btn btn-danger" href="{{ route('seller.services.list') }}">Cancel</a>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">Save & Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div id="ajaxCalls"></div>

@section('js')
<script>
    document.getElementById('toolbar').addEventListener('click', function(event) {
        event.preventDefault();
        var target = event.target;
        if (target.tagName === 'BUTTON') {
            var command = target.getAttribute('data-command');
            if (command === 'bullets') {
                insertBullets();
            } else {
                formatText(command);
            }
        }
    });

    function formatText(command) {
        document.execCommand(command, false, null);
    }

    function insertBullets() {
        var selection = window.getSelection();
        var range = selection.getRangeAt(0);
        var ul = document.createElement('ul');
        var li = document.createElement('li');
        range.surroundContents(ul);
        ul.appendChild(li);
    }

    function submitForm(event) {
        event.preventDefault(); // Prevent the form from submitting immediately

        // Get the content from the editor
        var content = document.getElementById('editor').innerHTML;

        // Remove unwanted formatting
        var strippedContent = stripFormatting(content);

        // Set the stripped content value to the hidden input field
        document.getElementById('content').value = strippedContent;

        // Submit the form
        event.target.submit();
    }

    function handlePaste(event) {
        event.preventDefault();
        var clipboardData = event.clipboardData || window.clipboardData;
        var pastedText = clipboardData.getData('text/plain');
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = pastedText;
        var strippedText = tempDiv.textContent || tempDiv.innerText;
        document.execCommand('insertText', false, strippedText);
    }

function stripFormatting(content) {
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;

    // Remove unwanted HTML formatting except for allowed tags
    var allowedTags = ['i', 'em', 'ul', 'b','div','p', 'li'];
    var elements = tempDiv.getElementsByTagName('*');
    for (var i = elements.length - 1; i >= 0; i--) {
        var element = elements[i];
        var tagName = element.tagName.toLowerCase();
        if (allowedTags.indexOf(tagName) === -1) {
            element.parentNode.removeChild(element);
        }
    }

    // Replace <div> tags with <p> tags
    var divElements = tempDiv.getElementsByTagName('div');
    for (var j = divElements.length - 1; j >= 0; j--) {
        var divElement = divElements[j];
        var p = document.createElement('p');
        p.innerHTML = divElement.innerHTML;
        divElement.parentNode.replaceChild(p, divElement);
    }

    // Replace <b> and <strong> tags with <span> tags to preserve bold formatting
    var boldElements = tempDiv.querySelectorAll('b, strong');
    for (var k = 0; k < boldElements.length; k++) {
        var boldElement = boldElements[k];
        var span = document.createElement('span');
        span.innerHTML = boldElement.innerHTML;
        boldElement.parentNode.replaceChild(span, boldElement);
    }

    // Return the stripped content
    return tempDiv.innerHTML;
}


    var createChecks = [];

    function removepreviewappended(id) {
        createChecks = createChecks.filter(function(value) {
            return value != id;
        });
        $('#fileappend-' + id).remove();
        $('#all_checks').val(createChecks);
    }

    $('.select2').select2({
        tags: true,
        maximumSelectionLength: 10,
        tokenSeparators: [','],
        placeholder: 'Select or type keywords',
    });

    function selectFileFromManagerMultiple(id, preview) {
        if ($('#file-' + id).hasClass('selected')) {
            $('#file-' + id).removeClass('selected')
                .find('.check-this').fadeOut();
            removepreviewappended(id);
        } else {
            $('#file-' + id).addClass('selected')
                .find('.check-this').fadeIn();
            createChecks.push(id);
            $('#fancyboxGallery').prepend(productImageDiv(id, preview));
        }
        $('#all_checks').val(createChecks);
    }
</script>
@endsection
