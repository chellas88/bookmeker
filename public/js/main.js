$(document).ready(function () {
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }
    );
    let pipelines = $('#audio_pipeline');
    let records = $('#audio_track');
    let statuses = $('#audio_pipeline_status');
    let sender = $('#sender');
    let triger;
    let callback_id;

    function hideError() {
        $('.error_msg').hide();
    }

    function showError(msg) {
        $('.error_msg').text(msg);
        $('.error_msg').show();
        setTimeout(hideError, 4000);
    }

    function loadData(pipeline_select, records_select, sender_select, additional_audio) {
        $.ajax({
            url: 'home/zvonobot_popup_data',
            method: 'post',
            data: {},
            success: function (response) {
                if (pipeline_select !== null) {
                    pipeline_select.empty();
                    pipeline_select.append('<option value="0">Выберите воронку</option>');
                    for (let key in response['pipelines']) {
                        pipeline_select.append('<option value="' + response['pipelines'][+key]['pipeline_id'] + '">' + response['pipelines'][+key]['pipeline_name'] + '</option>');
                    }
                }

                if (records_select !== null) {
                    records_select.empty();
                    records_select.append('<option value="0">Выберите аудиоролик</option>');
                    for (let key in response['records']) {
                        records_select.append('<option value="' + response['records'][+key]['id'] + '">' + response['records'][+key]['name'] + '</option>');
                    }
                }
                if (sender_select !== null) {
                    sender_select.empty();
                    sender_select.append('<option value="0">Выберите отправителя</option>')
                    for (let key in response['senders']) {
                        sender_select.append('<option value="' + response['senders'][+key]['id'] + '">' + response['senders'][+key]['sender'] + '</option>');
                    }
                }

                if (additional_audio !== null) {
                    additional_audio.empty();
                    additional_audio.append('<option value="0">Выберите аудиоролик</option>')
                    for (let key in response['records']) {
                        additional_audio.append('<option value="' + response['records'][+key]['id'] + '">' + response['records'][+key]['name'] + '</option>');
                    }
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    function loadStatuses(status_select, pipeline_id) {
        console.log(pipeline_id);
        status_select.empty();
        status_select.append('<option value="0">Выберите статус</option>');
        $.ajax({
            url: '/home/load_statuses',
            method: 'post',
            data: {'pipeline_id': pipeline_id},
            success: function (response) {
                for (let key in response) {
                    status_select.append('<option value="' + response[+key]['id'] + '">' + response[+key]['name'] + '</option>');
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    function getTasks(select) {
        select.empty();
        select.append('<option value="0">Выберите тип задачи</option>');
        $.ajax({
            url: '/home/get_tasks',
            method: 'get',
            data: {},
            success: function (response) {
                console.log(response);
                for (let key in response) {
                    select.append('<option value="' + response[+key]['id'] + '">' + response[+key]['name'] + '</option>');
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }


    $('#audio_delay_time').on('input', function (e) {
        this.value = this.value.replace(/[^0-9\.]/g, '');

    });
    $('#audio_delay_time_ed').on('input', function (e) {
        this.value = this.value.replace(/[^0-9\.]/g, '');

    });

    $("#press_digit").on('input', function (e) {
        this.value = this.value.replace(/[^0-9\.]/g, '');
    });
    $("#press_digit_ed").on('input', function (e) {
        this.value = this.value.replace(/[^0-9\.]/g, '');
    });

    $("#audio_id").on('input', function (e) {
        this.value = this.value.replace(/[^0-9\.]/g, '');
    });

    $('#ivr_show').on('click', function () {
        if ($(this).is(':checked')) {
            $('.ivr_block').show();
            if ($('#add_track').is(':checked')){
              $('.additional_audio').show();
            }
            if ($('#send_sms').is(':checked')){
              $('.sms_block').show();
            }
        } else {
            $('.ivr_block').hide();
            $('.additional_audio').hide();
            $('.sms_block').hide();
        }
    });

    $('#ivr_show_ed').on('click', function () {
        if ($(this).is(':checked')) {
            $('.ivr_block_ed').show();
            // $('.additional_audio_ed').show();
            // $('.sms_block_ed').show();
        } else {
            $('.ivr_block_ed').hide();
            // $('.additional_audio_ed').hide();
            // $('.sms_block_ed').hide();
        }
    });

    $('#callback_cb').on('click', function () {
        if ($(this).is(':checked')) {
            $('.callback_block').show();
            let event = $('#callback_event').val();
            if (event === 'status') {
                $('.callback_statuses').show();
                $('.callback_type_task').hide();
                $('.callback_note_text').hide();
            } else if (event === 'task') {
                $('.callback_statuses').hide();
                $('.callback_note_text').hide();
                $('.callback_type_task').show();
                getTasks($('#callback_task'));

            } else if (event === 'note') {
                $('.callback_statuses').hide();
                $('.callback_note_text').show();
                $('.callback_type_task').hide();
            } else {
                $('.callback_statuses').hide();
                $('.callback_note_text').hide();
                $('.callback_type_task').hide();
            }

        } else {
            $('.callback_block').hide();
            $('.callback_note_text').hide();
            $('.callback_type_task').hide();
            $('.callback_statuses').hide();
        }
    });

    $('#add_track').on('click', function () {
        if ($(this).is(':checked')) {
            $('.additional_audio').show();
        } else {
            $('.additional_audio').hide();
        }
    });

    $('#add_track_ed').on('click', function () {
        if ($(this).is(':checked')) {
            $('.additional_audio_ed').show();
        } else {
            $('.additional_audio_ed').hide();
        }
    });

    $('#send_sms').on('click', function () {
        if ($(this).is(':checked')) {
            $('.sms_block').show();
        } else {
            $('.sms_block').hide();
        }
    });

    $('#send_sms_ed').on('click', function () {
        if ($(this).is(':checked')) {
            $('.sms_block_ed').show();
        } else {
            $('.sms_block_ed').hide();
        }
    });

    $('#open_audio_triger_popup').on('click', function () {
        loadData(pipelines, records, sender, $('#ex_audio_track'));
        $('#audio_triger_name').val('');
        $('#gotoevent').val(0);
        $('#audio_event').val(0);
        $('#delay').hide();
        $('#sms_url').css('border', '').css('background', '');
        $('#send_sms').attr('checked', false);
        $('#sms_url').val('');
        $('#sms_body').val('');
    });

    $('#audio_add_btn').on('click', function () {
        let name = $('#audio_name').val();
        let id = $('#audio_id').val();
        if (name === "") {
            // $('#audio_name').css('border', "1px solid red");
            $('.error_msg').text("Ошибка: Введите название аудиоролика");
            $('.error_msg').show();
            setTimeout(hideError, 4000);
        } else if (id === "") {
            // $('#audio_id').css('border', "1px solid red");
            $('.error_msg').text("Ошибка: Введите корректный ID");
            $('.error_msg').show();
            setTimeout(hideError, 4000);
        } else if ((name !== "") && (id !== "")) {
            $.ajax({
                url: location.href + '/create_audio',
                method: 'post',
                data: {'name': name, 'id': id},
                success: function (response) {
                    if (response === 'success') {
                        document.location.href = '/home';
                    } else if (response === 'error') {
                        $('.error_msg').text("Ошибка: Триггер с таким именем уже существует");
                        $('.error_msg').show();
                        setTimeout(hideError, 4000);
                    }


                },
                error: function (response) {
                    console.log(response);
                    $('.error_msg').text("Неизвестная ошибка");
                    $('.error_msg').show();
                    setTimeout(hideError, 4000);
                }
            });
        }
    });

    pipelines.on('change', function () {
        let pipeline_id = $('option:selected', this).val();
        if (pipeline_id !== "0") {
            loadStatuses(statuses, pipeline_id);
            statuses.attr('disabled', false);
        } else {
            statuses.attr('disabled', true);
        }
    });

    $('#gotoevent').on('change', function () {
        let value = $('option:selected', this).val();
        if (value === "1") {
            $('#delay').show();
        } else if (value === "0") {
            $('#delay').hide();
        }
    });

    $('#audio_triger_add_btn').on('click', function () {
        let is_active;
        let send_sms;
        let ad_audio;
        let is_callback;
        let is_ivr;

        let name = $('#audio_triger_name').val();
        let event = $('#audio_event option:selected').val();
        let delay_type = $('#gotoevent').val();
        if (delay_type === "0") {
            delay_type = "now";
        } else {
            delay_type = $('#audio_delay_type').val();
        }
        let delay_time = $('#audio_delay_time').val();
        let pipeline_id = $('#audio_pipeline').val();
        let pipeline_name = $('#audio_pipeline option:selected').text();
        let status_id = $('#audio_pipeline_status').val();
        let status_name = $('#audio_pipeline_status option:selected').text();
        let record_id = $('#audio_track').val();
        let record_name = $('#audio_track option:selected').text();
        let sms_text = $('#sms_body').val();
        let sms_url = $('#sms_url').val();
        let sender_id = $('#sender').val();
        let sender_name = $('#sender option:selected').text();
        let digit = $('#press_digit').val();
        let sec_track = $('#ex_audio_track').val();
        let sec_track_name = $('#ex_audio_track option:selected').text();
        let callback_event = $('#callback_event').val();
        let callback_note = $('#callback_note').val();
        let callback_task = $('#callback_task').val();
        let callback_task_text = $('#callback_task_text').val();
        let callback_pipeline = $('#callback_pipeline').val();
        let callback_status = $('#callback_status').val();
        if ($('#audio_triger_activate').is(':checked')) {
            is_active = 1;
        } else {
            is_active = 0;
        }
        if ($('#send_sms').is(':checked')) {
            send_sms = 1;
        } else {
            send_sms = 0;
        }
        if ($('#add_track').is(':checked')) {
            ad_audio = 1;
        } else {
            ad_audio = 0;
        }
        if ($('#callback_cb').is(':checked')) {
            is_callback = 1;
        } else {
            is_callback = 0;
        }
        if ($('#ivr_show').is(':checked')) {
            is_ivr = 1;
        } else {
            is_ivr = 0;
        }

        if (name === "") {
            showError('Введите название триггера');
        } else if (event === "0") {
            showError('Выберите событие');
        } else if ((delay_type !== "now") && (delay_time === "")) {
            showError('Укажите время задержки');
        } else if (sender_id === "0") {
            showError('Выберите отправителя');
        } else if (pipeline_id === "0") {
            showError('Выберите воронку');
        } else if (status_id === "0") {
            showError('Выберите статус');
        } else if (record_id === "0") {
            showError('Выберите аудиоролик');
        } else if ((is_ivr === 1) && (digit === "")) {
            showError('Укажите цифру нажатия');
        } else if ((ad_audio === 1) && (sec_track === "0")) {
            showError('Выберите дополнительный аудиоролик');
        } else if ((send_sms === 1) && (sms_text === "")) {
            showError('Введите текст сообщения');
        } else if ((is_callback === 1) && (callback_event === "0")) {
            showError('Выберите callback событие');
        } else if ((is_callback === 1) && (callback_event === 'note') && (callback_note === "")) {
            showError('Укажите текст примечания');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task === "0")) {
            showError('Выберите тип задачи');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task_text === "")) {
            showError('Введите текст задачи');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_pipeline === "0")) {
            showError('Выберите воронку callback');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_status === "0")) {
            showError('Выберите статус воронки callback');
        } else {
            $.ajax({
                url: location.href + '/save_zvonobot_triger',
                method: 'post',
                data: {
                    'sender_id': sender_id,
                    'sender_name': sender_name,
                    'name': name,
                    'event': event,
                    'delay_type': delay_type,
                    'delay_time': delay_time,
                    'pipeline_id': pipeline_id,
                    'pipeline_name': pipeline_name,
                    'status_id': status_id,
                    'status_name': status_name,
                    'record_id': record_id,
                    'record_name': record_name,
                    'send_sms': send_sms,
                    'sms_text': sms_text,
                    'sms_url': sms_url,
                    'is_ivr': is_ivr,
                    'is_active': is_active,
                    'is_sec_record': ad_audio,
                    'sec_record_digit': digit,
                    'sec_record_id': sec_track,
                    'sec_record_name': sec_track_name,
                    'is_callback': is_callback,
                    'callback_event': callback_event,
                    'callback_pipeline': callback_pipeline,
                    'callback_status': callback_status,
                    'callback_note': callback_note,
                    'callback_task': callback_task,
                    'callback_task_text': callback_task_text
                },
                success: function (response) {
                    console.log(response);
                    document.location.href = '/home';
                },
                error: function (response) {
                    console.log(response);
                    showError('Произошла ошибка');
                }
            });
        }

    });


    //РЕДАКТИРОВАНИЕ
    $('#gotoevent_ed').on('change', function () {
        if ($('option:selected', this).val() === "0") {
            console.log('o');
            $('#delay_ed').hide();
        } else {
            $('#delay_ed').show();
        }
    });

    $('#audio_pipeline_ed').on('change', function () {
        let pipeline_id = $('option:selected', this).val();
        if (pipeline_id !== "0") {
            loadStatuses($('#audio_pipeline_status_ed'), pipeline_id);
            $('#audio_pipeline_status_ed').attr('disabled', false);
        } else {
            $('#audio_pipeline_status_ed').attr('disabled', true);
        }
    });

    $('.item_triger').on('click', function () {
        triger = $(this).attr('triger');
        console.log('triger' + triger);
        if (event.target.className === 'copy_url') {
            let callback = $(this).attr('callback');
            console.log('callback is ' + callback);
            //Копируем ссылку по нажатию кнопки
            let url = $('#callback_' + callback).attr('url');
            $('#hidden_url').val(url);
            $('#hidden_url').select();
            document.execCommand("copy");
            console.log($('#hidden_url').val());
            alert('Callback URL был скопирован')

        } else {
            //Открываем попап с инфой по тригеру
            $('#sms_url_ed').css('border', '').css('background', '');
            loadData($('#audio_pipeline_ed'), $('#audio_track_ed'), $('#sender_ed'), $('#ex_audio_track_ed'));
            triger = $(this).attr("triger");
            $('#edit_triger_zvonobot').trigger('click');
            $.ajax({
                url: location.href + '/open_triger',
                method: 'post',
                data: {'triger_id': triger},
                success: function (response) {
                    loadStatuses($('#audio_pipeline_status_ed'), String(response['pipeline_id']));
                    console.log(response);
                    $('#sms_body_ed').val(response['sms_text']);
                    $('#sms_url_ed').val(response['sms_url']);
                    $('#audio_triger_name_ed').attr('value', response['name']);
                    $('#audio_event_ed').val(response['event']);
                    if (response['delay_type'] === "now") {
                        $('#gotoevent_ed').val("0");
                        $('#delay_ed').hide();
                    } else if (response['delay_type'] === 'minutes') {
                        $('#gotoevent_ed').val("1");
                        $('#audio_delay_time_ed').val(response['delay_time']);
                        $('#audio_delay_type_ed').val(response['delay_type']);
                        $('#delay_ed').show();
                    } else if (response['delay_type'] === 'hours') {
                        $('#gotoevent_ed').val("1");
                        $('#audio_delay_time_ed').val(response['delay_time']);
                        $('#audio_delay_type_ed').val(response['delay_type']);
                        $('#delay_ed').show();
                    } else if (response['delay_type'] === 'days') {
                        $('#gotoevent_ed').val("1");
                        $('#audio_delay_time_ed').val(response['delay_time']);
                        $('#audio_delay_type_ed').val(response['delay_type']);
                        $('#delay_ed').show();
                    }


                    setTimeout(function () {
                        $('#sender_ed').val(response['sender_id'])
                        $('#audio_pipeline_ed').val(response['pipeline_id'])
                        $('#audio_track_ed').val(response['record_id'])
                        $('#audio_pipeline_status_ed').val(response['status_id'])
                    }, 1200);

                    //IVR
                    if (response['is_ivr'] === 1) {
                        $('#ivr_show_ed').prop('checked', true);
                        $('.ivr_block_ed').show();
                        $('#press_digit_ed').val(response['sec_record_digit']);

                    } else {
                        $('#ivr_show_ed').prop('checked', false);
                        $('.ivr_block_ed').hide();
                    }
                    //SMS
                    if (response['send_sms'] === 1) {
                        $('#send_sms_ed').prop('checked', true);
                        $('.sms_block_ed').show();

                    } else {
                        $('#send_sms_ed').prop('checked', false);
                        $('.sms_block_ed').hide()

                    }

                    //Activate
                    if (response['is_active'] === 1) {
                        $('#audio_triger_activate_ed').prop('checked', true)
                    } else {
                        $('#audio_triger_activate_ed').prop('checked', false)
                    }

                    //Audiotrack ext
                    if (response['is_sec_record'] === 1) {
                        $('#add_track_ed').prop('checked', true);
                        setTimeout(function () {
                            $('#ex_audio_track_ed').val(response['sec_record_id'])
                        }, 1200);
                        $('.additional_audio_ed').show();
                    } else {
                        $('#add_track_ed').prop('checked', false);
                        $('.additional_audio_ed').hide();
                        $('#ex_audio_track_ed').val('0');
                    }
                    //Callback
                    if (response['is_callback'] === 1) {
                        let event = response['callback_event'];
                        $('#callback_cb_ed').prop('checked', true);
                        $('.callback_block_ed').show();
                        $('#callback_event_ed').val(event);
                        if (event === 'task') {
                            $('.callback_statuses_ed').hide();
                            $('.callback_note_text_ed').hide();
                            getTasks($('#callback_task_ed'));
                            setTimeout(function () {
                                $('#callback_task_ed').val(response['callback_task'])
                            }, 1200);
                            $('.callback_type_task_ed').show();
                            $('#callback_task_text_ed').val(response['callback_task_text']);
                        } else if (event === 'note') {
                            $('.callback_statuses_ed').hide();
                            $('.callback_type_task_ed').hide();
                            $('.callback_note_text_ed').show();
                            $('#callback_note_ed').val(response['callback_note']);
                        } else if (event === 'status') {
                            loadStatuses($('#callback_status_ed'), response['callback_pipeline']);
                            $('.callback_type_task_ed').hide();
                            $('.callback_note_text_ed').hide();
                            $('.callback_statuses_ed').show();
                            setTimeout(function () {
                                $('#audio_pipeline_ed option').clone().appendTo($('#callback_pipeline_ed'));
                                $('#callback_pipeline_ed').val(response['callback_pipeline']);
                                $('#callback_status_ed').val(response['callback_status']);
                            }, 1200);

                        }
                    } else {
                        $('#callback_cb_ed').prop('checked', false);
                        $('.callback_block_ed').hide();
                        $('.callback_type_task_ed').hide();
                        $('.callback_note_text_ed').hide();
                        $('.callback_statuses_ed').hide();
                    }

                },
                error: function (response) {
                    console.log(response);

                }
            });
        }
    });


    $('#audio_triger_update_btn').on('click', function () {
        let is_active;
        let send_sms;
        let is_callback;
        let is_audio;
        let is_ivr;
        let name = $('#audio_triger_name_ed').val();
        let event = $('#audio_event_ed option:selected').val();
        let delay_type = $('#gotoevent_ed').val();
        if (delay_type === "0") {
            delay_type = "now";
        } else {
            delay_type = $('#audio_delay_type_ed').val();
        }
        let delay_time = $('#audio_delay_time_ed').val();
        let pipeline_id = $('#audio_pipeline_ed').val();
        let pipeline_name = $('#audio_pipeline_ed option:selected').text();
        let status_id = $('#audio_pipeline_status_ed').val();
        let status_name = $('#audio_pipeline_status_ed option:selected').text();
        let record_id = $('#audio_track_ed').val();
        let record_name = $('#audio_track_ed option:selected').text();
        let sms_text = $('#sms_body_ed').val();
        let sms_url = $('#sms_url_ed').val();
        let sender_id = $('#sender_ed').val()
        let sender_name = $('#sender_ed option:selected').text();
        let sec_record_id = $('#ex_audio_track_ed').val();
        let sec_record_name = $('#ex_audio_track_ed option:selected').text();
        let digit = $('#press_digit_ed').val();
        let callback_pipeline = $('#callback_pipeline_ed').val();
        let callback_status = $('#callback_status_ed').val();
        let callback_task = $('#callback_task_ed').val();
        let callback_task_text = $('#callback_task_text_ed').val();
        let callback_event = $('#callback_event_ed').val();
        let callback_note = $('#callback_note_ed').val();


        if ($('#audio_triger_activate_ed').is(':checked')) {
            is_active = 1;
        } else {
            is_active = 0;
        }
        if ($('#send_sms_ed').is(':checked')) {
            send_sms = 1;
        } else {
            send_sms = 0;
        }

        if ($('#callback_cb_ed').is(':checked')) {
            is_callback = 1;
        } else {
            is_callback = 0;
        }
        if ($('#add_track_ed').is(':checked')) {
            is_audio = 1;
        } else {
            is_audio = 0;
        }
        if ($('#ivr_show_ed').is(':checked')) {
            is_ivr = 1;
        } else {
            is_ivr = 0;
        }
        let path = location.href;
        if (name === "") {
            showError('Введите название триггера');
        } else if (event === "0") {
            showError('Выберите событие');
        } else if ((delay_type !== "now") && (delay_time === "")) {
            showError('Укажите задержку выполнения');
        } else if (pipeline_id === "0") {
            showError('Выберите воронку');
        } else if (status_id === "0") {
            showError('Выберите статус воронки');
        } else if (sender_id === "0") {
            showError('Выберите отправителя');
        } else if (record_id === "0") {
            showError('Выберите аудиоролик');
        } else if ((is_ivr === 1) && (digit === "")) {
            showError('Укажите цифру нажатия');
        } else if ((is_audio === 1) && (sec_record_id === "0")) {
            showError('Выберите дополнительный аудиоролик');
        } else if ((send_sms === 1) && (sms_text === "")) {
            showError('Введите текст сообщения');
        } else if ((is_callback === 1) && (callback_event === "0")) {
            showError('Выберите callback событие');
        } else if ((is_callback === 1) && (callback_event === 'note') && (callback_note === "")) {
            showError('Укажите текст примечания');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task === "0")) {
            showError('Выберите тип задачи');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task_text === "")) {
            showError('Введите текст задачи');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_pipeline === "0")) {
            showError('Выберите воронку callback');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_status === "0")) {
            showError('Выберите статус воронки callback');
        } else {
            $.ajax({
                url: path + '/update_triger',
                method: 'post',
                data: {
                    'sender_name': sender_name,
                    'sec_record_id': sec_record_id,
                    'sec_record_name': sec_record_name,
                    'digit': digit,
                    'callback_pipeline': callback_pipeline,
                    'callback_status': callback_status,
                    'callback_task': callback_task,
                    'callback_event': callback_event,
                    'callback_task_text': callback_task_text,
                    'callback_note': callback_note,
                    'is_audio': is_audio,
                    'is_callback': is_callback,
                    'sender_id': sender_id,
                    'id': triger,
                    'name': name,
                    'event': event,
                    'delay_type': delay_type,
                    'delay_time': delay_time,
                    'pipeline_id': pipeline_id,
                    'pipeline_name': pipeline_name,
                    'status_id': status_id,
                    'status_name': status_name,
                    'record_id': record_id,
                    'record_name': record_name,
                    'send_sms': send_sms,
                    'sms_text': sms_text,
                    'sms_url': sms_url,
                    'is_active': is_active,
                    'is_ivr': is_ivr
                },
                success: function (response) {


                    document.location.href = path;
                },
                error: function (response) {
                    console.log(response);
                    showError("Произошла ошибка");
                }
            });
        }
    });


    $('#sms_triger_add_btn').on('click', function () {
        let is_active;
        let is_callback;
        let name = $('#audio_triger_name').val();
        let event = $('#audio_event option:selected').val();
        let delay_type = $('#gotoevent').val();
        if (delay_type === "0") {
            delay_type = "now";
        } else {
            delay_type = $('#audio_delay_type').val();
        }
        let delay_time = $('#audio_delay_time').val();
        let pipeline_id = $('#audio_pipeline').val();
        let pipeline_name = $('#audio_pipeline option:selected').text();
        let status_id = $('#audio_pipeline_status').val();
        let status_name = $('#audio_pipeline_status option:selected').text();
        let sms_text = $('#sms_body').val();
        let sms_url = $('#sms_url').val();
        let sender_id = $('#sender').val();
        let sender_name = $('#sender option:selected').text();
        let callback_event = $('#callback_event').val();
        let callback_pipeline = $('#callback_pipeline').val();
        let callback_status = $('#callback_status').val();
        let callback_note = $('#callback_note').val();
        let callback_task = $('#callback_task').val();
        let callback_task_text = $('#callback_task_text').val();

        if ($('#audio_triger_activate').is(':checked')) {
            is_active = 1;
        } else {
            is_active = 0;
        }
        if ($('#callback_cb').is(':checked')) {
            is_callback = 1;
        } else {
            is_callback = 0;
        }

        if (name === "") {
            showError('Введите название триггера');
        } else if (event === "0") {
            showError('Выберите событие');
        } else if ((delay_type !== "now") && (delay_time === "")) {
            showError('Укажите время задержки');
        } else if (sender_id === "0") {
            showError('Выберите отправителя');
        } else if (pipeline_id === "0") {
            showError('Выберите воронку');
        } else if (status_id === "0") {
            showError('Выберите статус');
        } else if (sms_text === "") {
            showError('Введите текст сообщения');
        } else if ((is_callback === 1) && (callback_event === "0")) {
            showError('Выберите callback событие');
        } else if ((is_callback === 1) && (callback_event === 'note') && (callback_note === "")) {
            showError('Укажите текст примечания');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task === "0")) {
            showError('Выберите тип задачи');
        } else if ((is_callback === 1) && (callback_event === 'task') && (callback_task_text === "")) {
            showError('Введите текст задачи');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_pipeline === "0")) {
            showError('Выберите воронку callback');
        } else if ((is_callback === 1) && (callback_event === 'status') && (callback_status === "0")) {
            showError('Выберите статус воронки callback');
        } else {
            $.ajax({
                url: location.href + '/save_triger',
                method: 'post',
                data: {
                    'sender_id': sender_id,
                    'sender_name': sender_name,
                    'name': name,
                    'event': event,
                    'delay_type': delay_type,
                    'delay_time': delay_time,
                    'pipeline_id': pipeline_id,
                    'pipeline_name': pipeline_name,
                    'status_id': status_id,
                    'status_name': status_name,
                    'sms_text': sms_text,
                    'sms_url': sms_url,
                    'is_active': is_active,
                    'is_callback': is_callback,
                    'callback_event': callback_event,
                    'callback_pipeline': callback_pipeline,
                    'callback_status': callback_status,
                    'callback_task': callback_task,
                    'callback_task_text': callback_task_text,
                    'callback_note': callback_note
                },
                success: function (response) {
                    document.location.href = '/sms';
                },
                error: function (response) {
                    console.log(response);
                    showError("Произошла ошибка");
                }
            });
        }

    });

    let current_pipeline;

    $('#show_filtr').on('click', function () {
        $('.filtr_content').toggle('slow');
        let status = this.innerText;
        if (status === 'Показать фильтр') {
            this.innerText = 'Скрыть фильтр';
        } else {
            this.innerText = 'Показать фильтр';
        }
        $.ajax({
            url: 'home/zvonobot_popup_data',
            method: 'post',
            success: function (response) {
                $('.filtr_pipeline').empty();
                $('.filtr_pipeline').append('<option value="0">Выберите воронку</option>');
                for (let key in response['pipelines']) {
                    $('.filtr_pipeline').append('<option value="' + response['pipelines'][+key]['pipeline_id'] + '">' + response['pipelines'][+key]['pipeline_name'] + '</option>');
                }
                if (current_pipeline) {
                    $('.filtr_pipeline').val(current_pipeline);
                }

            }
        });
    });

    $('.filtr_pipeline').on('change', function () {
        $('.filtr_status').empty();
        $('.filtr_status').hide();
        let pipeline_id = $(this).val();
        current_pipeline = pipeline_id;
        $.ajax({
            url: '/home/load_statuses',
            method: 'post',
            data: {'pipeline_id': pipeline_id},
            success: function (response) {
                $('.filtr_status').append('<h3>Статусы воронки</h3>');
                $('.filtr_status').append('<p><input class="pipeline_item_all" type="checkbox" id="all_pipelines"><label for="all_pipelines">Выбрать все</label></p>');

                for (let key in response) {
                    $('.filtr_status').append('<p><input class="pipeline_item" type="checkbox" id="' + response[+key]["id"] + '"><label for="' + response[+key]["id"] + '">' + response[+key]["name"] + '</label></p>');
                    if (pipeline_id !== "0") {
                        $('.filtr_status').show();
                    }
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });

    $('.filtr_status').on('click', '#all_pipelines', function () {

      if (this.checked) {
        $('.filtr_status').find('.pipeline_item').each(function () {
          $(this).prop('checked', true);
        });

      } else {
        $('.filtr_status').find('.pipeline_item').each(function () {
          $(this).prop('checked', false);
        });
      }

    });

    $('.filtr_status').on('click', '.pipeline_item', function () {

      var all_pipelines_checkbox = $('.filtr_status').find('#all_pipelines');

      if (all_pipelines_checkbox.prop('checked') === true){
        all_pipelines_checkbox.prop('checked', false);
      }

    });

    $('#filtr_add').on('click', function () {
        $('#current_page').text('1');
        let show = $('#leads_count').val();

        getLeads(show, 1);
        $('#show_filtr').trigger('click');
    });

    $('#next').on('click', function () {
        let page = $('#current_page').text();
        let show = $('#leads_count').val();

        ++page;
        getLeads(+show, page);
        $('#current_page').text(page);
        if (page > 1) {
            $('#prev').removeClass('disabled');
        } else {

        }
    });

    $('#prev').on('click', function () {
        let page = $('#current_page').text();
        let show = $('#leads_count').val();
        --page;
        getLeads(+show, page);
        $('#current_page').text(page);
        if (page === 1) {
            $('#prev').addClass('disabled');
        } else {
            $('#current_page').text(page);
        }
    });

var data_filter = {};

    function getLeads(show, page) {
        $('.page_content').hide();
        $('.loader').show();
        $('.global_leads_table tbody').empty();
        let pipeline = $('.filtr_pipeline').val();
        let created_from = $('#filtr_created_from').val();
        let created_to = $('#filtr_created_to').val();
        let update_from = $('#filtr_updated_from').val();
        let update_to = $('#filtr_updated_to').val();
        let statuses = [];
        let filtr = {};
        filtr['pipeline_id'] = pipeline;
        $('.pipeline_item').each(function () {
            if (this.checked) {
                statuses.push(this.getAttribute("id"));
            }
        });
        filtr['statuses'] = statuses;
        filtr['created_from'] = created_from;
        filtr['created_to'] = created_to;
        filtr['update_from'] = update_from;
        filtr['update_to'] = update_to;


        if (show === "all") {
            data = {'filtr': filtr, 'show': 'all'};
        } else {
            data = {'filtr': filtr, 'page': page, 'show': show};
        }

        if (filtr['pipeline_id'] !== '0' && filtr['statuses'][0] === undefined){
          $('.pipeline_item').each(function () {
            filtr['statuses'].push(this.getAttribute("id"));
          });
        }
        let count = 0;

        data_filter = data.filtr;
        console.log(data);
        $.ajax({
            url: '/amo/filter',
            method: 'post',
                data: data,
            success: function (response) {
              var table_line = '';
                for (let key in response) {

                var table_line = '<tr>'+
                  '<td align="center" class="checkbox_lead">'+
                    '<input class="lead_item" type="checkbox" id="' + response[+key]['lead_id'] + '">'+
                  '</td>'+
                  '<td class="title_lead">' + response[+key]['lead_name']+ '</td>'+
                  '<td>' + response[+key]['pipeline_name']+ '</td>'+
                  '<td>' + response[+key]['status_name']+ '</td>'+
                  '<td>' + response[+key]['created_at']+ '</td>'+
                  '<td>' + response[+key]['updated_at']+ '</td>'+
                  '<td> <a href="https://00000007css.amocrm.ru/leads/detail/' + response[+key]['lead_id'] + '" target="_blank">[Ссылка]</a> </td>'+
                '</tr>';


                  $('.global_leads_table tbody').append(table_line);
                  ++count;
                }
                $('#select_all').text('Отметить все (' + count + ')');
                if (count > 0) {
                    $('.leads_menu').show();
                } else {
                    $('.leads_menu').hide();
                }
                if (show === "all") {
                    $('#next').addClass('disabled');
                } else if (count < show) {
                    $('#next').addClass('disabled');
                } else {
                    $('#next').removeClass('disabled');
                }
                $('.loader').hide();
                $('.settings_filter_text').hide();
                $('.page_content').show();
                $('.global_leads_table').show();


            },
            error: function (response) {
                $('.leads').append('<p>Ничего не найдено</p>');
                $('.loader').hide();
                $('.page_content').show();
                $('#next').addClass('disabled');
            }
        });
    }


    $('#filtr_created_reset').on('click', function () {
        $('#filtr_created_from').val('');
        $('#filtr_created_to').val('');
    });

    $('#filtr_updated_reset').on('click', function () {
        $('#filtr_updated_from').val('');
        $('#filtr_updated_to').val('');
    });

    $('#filtr_reset').on('click', function () {
        $('#filtr_created_from').val('');
        $('#filtr_created_to').val('');
        $('#filtr_updated_from').val('');
        $('#filtr_updated_to').val('');
        $('.filtr_pipeline').val('0');
        $('.filtr_status').css('display', 'none');
    });

    $('#leads_count').on('change', function () {
        $('#current_page').text('1');
        $('#prev').addClass('disabled');
        $('#next').removeClass('disabled');
        let show = $(this).val();
        getLeads(show, 1);
    });

    $('#select_all').on('click', function () {
        $('.lead_item').prop('checked', true);

    });

    $('#unselect_all').on('click', function () {
        $('.lead_item').prop('checked', false);

    });

    $('#autosender').on('click', function () {
        loadData(null, null, $('#sender'), null);
    });

    $('#autocalling').on('click', function () {
        loadData(null, $('#track_z'), $('#sender_z'), $('#ex_audio_track_z'));
    });


    $('#add_autosender').on('click', function () {
        let name = $('#name').val();
        let sender_id = $('#sender').val();
        let sender_name = $("#sender").find(":selected").text();
        let sms_text = $('#sms_body').val();
        let sms_url = $('#sms_url').val();
        let data;
        let leads = [];
        $('.lead_item').each(function () {
            if (this.checked) {
                leads.push(this.getAttribute("id"));
            }
        });
        if ($('input[name=select]:checked').val() === "all") {
            data = {
                'name': name,
                'sender_id': sender_id,
                'sender_name': sender_name,
                'sms_text': sms_text,
                'sms_url': sms_url,
                'destination': 'all'
            };
        } else {
            data = {
                'name': name,
                'sender_id': sender_id,
                'sender_name': sender_name,
                'sms_text': sms_text,
                'sms_url': sms_url,
                'destination': 'list',
                'leads': leads
            };
        }
        if (name === "") {
            showError("Введите название рассылки");
        } else if (sender_id === "0") {
            showError("Выберите имя отправителя");
        } else if (sms_text === "") {
            showError("Введите текст сообщения");
        } else if (data.destination === "list" && data.leads[0] === undefined) {
            showError("Не выбрано, кому отправлять рассылку");
        } else {
            data.filters = JSON.stringify(data_filter);
            $.ajax({
                url: '/global/save_autosender',
                method: 'post',
                data: data,
                success: function (response) {
                    console.log(response);
                    document.location.href = "/global";
                },
                error: function (response) {
                    console.log(response);
                    showError("Произошла ошибка");
                }
            });
        }

    });

    $('.form_autocaller').on('submit', function (e) {
        e.preventDefault();
        var me = this;
        var form = $(this);
        var formData = getFormData(me);
        var form_is_validate = true;
        let leads = [];
        $('.lead_item').each(function () {
            if (this.checked) {
                leads.push(this.getAttribute("id"));
            }
        });

        if (formData.sender_id != 0) {
            formData.sender_name = form.find("select[name$='sender_id']").find(":selected").text();
        } else {
            formData.sender_name = '';
        }
        if (formData.destination === 'list') {
            formData.leads = leads;
        }

        if (formData.is_audio !== undefined) {
            formData.is_audio = 1;
        } else {
            formData.is_audio = 0;
        }
        if (formData.is_sms !== undefined) {
            formData.is_sms = 1;
        } else {
            formData.is_sms = 0;
        }
        if (formData.is_ivr !== undefined) {
            formData.is_ivr = 1;
        } else {
            formData.is_ivr = 0;
            formData.is_sms = 0;
            formData.is_audio = 0;
        }


        if (formData.name === "") {
            showError('Введите название прозвона');
        } else if (formData.audio === "0") {
            showError('Выберите аудиоролик');
        } else if (formData.is_ivr === 1 && formData.digit === "") {
            showError('Укажите цифру нажатия');
        } else if (formData.is_audio === 1 && formData.sec_audio === "0") {
            showError('Выберите дополнительный аудиоролик');
        } else if (formData.is_sms === 1 && formData.sender_id === "0") {
            showError('Выберите отправителя');
        } else if (formData.is_sms === 1 && formData.sms_text === "") {
            showError('Введите текст сообщения');
        } else if (formData.destination === "list" && formData.leads[0] === undefined) {
            showError("Не выбрано, кому отправлять рассылку");
        } else {
            formData.filters = JSON.stringify(data_filter);
            $.ajax({
                url: '/global/save_autocaller',
                method: 'post',
                data: formData,
                success: function (response) {
                    console.log(response);
                    document.location.href = '/global';
                },
                error: function (response) {
                    console.log(response);
                    showError('Произошла ошибка');
                }
            });
        }
    });

    $('#autocaller_popup').on('hidden.bs.modal', function () {
      var inputs = $(this).find('input');
      clearInputs(inputs);

      $('.ivr_block').hide();
      $('.additional_audio').hide();
      $('.sms_block').hide();
    });

    $('#autosender_popup').on('hidden.bs.modal', function () {
      var inputs = $(this).find('input');
      clearInputs(inputs);
    });

    function getFormData(form) {
        var data = {};
        var dataArray = $(form).serializeArray();
        for (var i = 0; i < dataArray.length; i++) {
            data[dataArray[i].name] = dataArray[i].value;
        }
        return data;
    }

    function clearInputs(inputs){
      if (inputs[0] !== undefined){
        for (var i = 0; i < inputs.length; i++) {
            var input = $(inputs[i]);
            if (input.attr('type') === 'text'){
              input.val('');
            } else if (input.attr('type') === 'checkbox'){
              input.prop('checked', false);
            } else if (input.attr('type') === 'radio'){
              var radio_name = input.attr('name');
              $(inputs.filter('[name='+radio_name+']')[0]).prop('checked', true);
            }
        }
      }
    }

    $('#link_show').on('click', function () {
        if ($(this).is(':checked')) {
            $('.link_block').show();
        } else {
            $('.link_block').hide();
        }
    });

    $('.autoBotSender_form').on('submit', function (e) {
        e.preventDefault();
        var me = this;
        var form = $(this);
        var formData = getFormData(me);
        var form_is_validate = true;
        let leads = [];
        $('.lead_item').each(function () {
            if (this.checked) {
                leads.push(this.getAttribute("id"));
            }
        });

        if (formData.destination === 'list') {
            formData.leads = leads;
        }

        if (formData.is_link !== undefined) {
            formData.is_link = 1;
        } else {
            formData.is_link = 0;
        }


        if (formData.name === "") {
            showError('Введите название авторассылки');
        } else if (formData.driver === "0") {
            showError('Выберите мессенджер');
        } else if (formData.type_message === '0') {
            showError('Выберите тип сообщения');
        } else if (formData.type_message === 'template' && formData.template === "0") {
            showError('Выберите шаблон сообщения');
        } else if (formData.type_message === 'new_message' && formData.text_message === "") {
            showError('Введите текст сообщения');
        } else if (formData.type_message === 'new_message' && formData.is_link === 1 && formData.link === "") {
            showError('Введите ссылку');
        } else if (formData.destination === "list" && formData.leads[0] === undefined) {
            showError("Не выбрано, кому отправлять рассылку");
        } else {
            formData.filters = JSON.stringify(data_filter);
            console.log(formData);
            $.ajax({
                url: '/global/save_autobotsender',
                method: 'post',
                data: formData,
                success: function (response) {
                    document.location.href = '/global';
                },
                error: function (response) {
                    showError('Произошла ошибка');
                }
            });
        }
    });


    $('#autoBotSender_popup').on('hidden.bs.modal', function () {
      var inputs = $(this).find('input');
      clearInputs(inputs);

      $(this).find('select').val('0');
      $(this).find('textarea').val('');
      $(this).find('.emoji-wysiwyg-editor').text('');

      $('.link_block').hide();
    });

    $('#autoBotSender_popup').on('show.bs.modal', function () {
      getTemplates($(this).find('.template'));
    });

    $('.type_message').on('change', function(){
      var value = $(this).val();
      $('.new_message_wrap').hide();
      $('.link_block').hide();
      $('.template_wrap').hide();
      if (value === 'new_message'){
        $('.new_message_wrap').show();
        if ($('#link_show').is(':checked')){
          $('.link_block').show();
        }
      } else if (value === 'template'){
        $('.template_wrap').show();
      }
    });


    // templates

    function getTemplates(select) {
        select.empty();
        select.append('<option value="0">Выберите шаблон</option>');
        $.ajax({
            url: '/templates/all',
            method: 'post',
            data: {},
            success: function (response) {
                // console.log(response);
                for (let key in response) {
                  var sliced = response[+key]['name'].slice(0,25);
                  if (sliced.length < response[+key]['name'].length) {
                    sliced += '...';
                  }
                    select.append('<option value="' + response[+key]['id'] + '">' + sliced + '</option>');
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    $('.template_form').on('submit', function (e) {
        e.preventDefault();
        var me = this;
        var form = $(this);
        var formData = getFormData(me);
        var form_is_validate = true;
        var url = '';

        if (formData.is_url !== undefined) {
            formData.is_url = 1;
        } else {
            formData.is_url = 0;
        }

        if (formData.name === "") {
            showError('Введите название шаблона');
        } else if (formData.text_message === "") {
            showError('Введите текст сообщения');
        } else if (formData.is_url === 1 && formData.url === "") {
            showError('Введите ссылку');
        } else {
            console.log(formData);
            $.ajax({
                url: '/templates/'+formData.action,
                method: 'post',
                data: formData,
                success: function (response) {
                  // console.log(response);
                    document.location.href = '/templates';
                },
                error: function (response) {
                    showError('Произошла ошибка');
                }
            });
        }
    });


    $('#template_popup').on('hidden.bs.modal', function () {
      var inputs = $(this).find('input');
      clearInputs(inputs);

      $(this).find('select').val('0');
      $(this).find('textarea').val('');
      $(this).find('.emoji-wysiwyg-editor').text('');

      $('.link_block').hide();
    });

    $('#create_template_btn').on('click', function(){
      var form = $('.template_form');
      var popup = $('#template_popup');
      form.find('[name=action]').val('create');
      popup.find('.title').text('Новый шаблон');
      popup.find('.add').text('Добавить');

      var inputs = form.find('input');
      clearInputs(inputs);

      form.find('select').val('0');
      form.find('textarea').val('');
      form.find('.emoji-wysiwyg-editor').text('');

      form.find('.link_block').hide();
    });

    $('.item_template').on('click', function (e) {
        let id = $(this).attr('id');
        let name = $(this).attr('name');
        var open_popup_update = true;
        for (var i=0; i<e.target.classList.length; i++){
          if (e.target.classList[i] === 'delete_template' || e.target.classList[i] === 'fa-trash'){
            open_popup_update = false;
            var delete_popup = $('#delete_popup');
            delete_popup.find('.name_template').text(name);
            delete_popup.find('.id_template').text(id);
            delete_popup.find('.delete_template_btn').attr('id', id);
            var delete_popup_bootstrap = new bootstrap.Modal(document.getElementById('delete_popup'), {});
            delete_popup_bootstrap.show();
          }
        }

        if (open_popup_update === true){
          var form = $('.template_form');
          var popup = $('#template_popup');
          form.find('[name=action]').val('update');
          popup.find('.title').text('Обновить шаблон');
          popup.find('.add').text('Обновить');
          var template_popup = new bootstrap.Modal(document.getElementById('template_popup'), {});

          $.ajax({
              url: '/templates/get_template_by_id',
              method: 'post',
              data: {'id': id},
              success: function (response) {
                  console.log(response);
                  form.find('[name=id]').val(response.id);
                  form.find('[name=name]').val(response.name);
                  form.find('[name=text_message]').val(response.message);
                  var arrayMessage = response.message.split('\r\n');
                  var messageForEditor = arrayMessage[0];
                  for (i=1; i<arrayMessage.length; i++){
                    if (arrayMessage[i] === ''){
                      messageForEditor += '<div><br></div>';
                    } else {
                      messageForEditor += '<div>'+arrayMessage[i]+'</div>';
                    }
                  }
                  form.find('.emoji-wysiwyg-editor').html(messageForEditor);
                  if (response.is_url === 1){
                    form.find('[name=is_url]').prop('checked', true);
                    form.find('[name=url]').val(response.url);
                    form.find('.link_block').show();
                  }
                  template_popup.show();
              },
              error: function (response) {
                  console.log(response);
              }
          });

        } else {

        }
    });

    $('#delete_popup').find('.delete_template_btn').on('click', function (e) {
      console.log('delete_template_btn');
      let id = $(this).attr('id');
      $.ajax({
          url: '/templates/delete',
          method: 'post',
          data: {'id': id},
          success: function (response) {
              document.location.href = '/templates';
          },
          error: function (response) {
              console.log(response);
          }
      });
    });


    //Callbacks

    $('#callback_event').on('change', function () {
        let event = $('option:selected', this).val();
        if (event === 'status') {
            loadData($('#callback_pipeline'), null, null, null);
            $('.callback_statuses').show();
            $('.callback_type_task').hide();
            $('.callback_note_text').hide();
            $('#callback_task').val('0');
            $('#callback_note').val('');
            $('#callback_task_text').val('');
        } else if (event === 'task') {
            $('.callback_statuses').hide();
            $('.callback_note_text').hide();
            $('.callback_type_task').show();
            getTasks($('#callback_task'));
            $('#callback_note').val('');
            $('#callback_status').val('0');
            $('#callback_pipeline').val('0');
        } else if (event === 'note') {
            $('.callback_statuses').hide();
            $('.callback_note_text').show();
            $('.callback_type_task').hide();
            $('#callback_task').val('0');
            $('#callback_status').val('0');
            $('#callback_pipeline').val('0');
            $('#callback_task_text').val('');

        } else {
            $('.callback_statuses').hide();
            $('.callback_note_text').hide();
            $('.callback_type_task').hide();
            $('#callback_task').val('0');
            $('#callback_status').val('0');
            $('#callback_pipeline').val('0');
            $('#callback_note').val('');
            $('#callback_task_text').val('');

        }
    });

    $('#callback_pipeline').on('change', function () {
        loadStatuses($('#callback_status'), $(this).val());
    });

    $('#callback_pipeline_ed').on('change', function () {
        loadStatuses($('#callback_status_ed'), $(this).val());
    });


    $('#callback_event_ed').on('change', function () {
        let event = $('option:selected', this).val();
        console.log(event);
        if (event === 'status') {
            loadData($('#callback_pipeline_ed'), null, null, null);
            $('.callback_statuses').show();
            $('.callback_type_task').hide();
            $('.callback_note_text').hide();
            $('#callback_task_ed').val('0');
            $('#callback_note_ed').val('');
            $('#callback_task_text_ed').val('');
        } else if (event === 'task') {
            $('.callback_statuses').hide();
            $('.callback_note_text').hide();
            $('.callback_type_task').show();
            getTasks($('#callback_task_ed'));
            $('#callback_note_ed').val('');
            $('#callback_status_ed').val('0');
            $('#callback_pipeline_ed').val('0');
        } else if (event === 'note') {
            $('.callback_statuses').hide();
            $('.callback_note_text').show();
            $('.callback_type_task').hide();
            $('#callback_task_ed').val('0');
            $('#callback_status_ed').val('0');
            $('#callback_pipeline_ed').val('0');
            $('#callback_task_text_ed').val('');

        } else {
            $('.callback_statuses_ed').hide();
            $('.callback_note_text_ed').hide();
            $('.callback_type_task_ed').hide();
            $('#callback_task_ed').val('0');
            $('#callback_status_ed').val('0');
            $('#callback_pipeline_ed').val('0');
            $('#callback_note_ed').val('');
            $('#callback_task_text_ed').val('');

        }
    });


    $('#callback_triger_type').on('change', function () {
        let triger_type = $(this).val();
        let select = $('#callback_triger');
        if (triger_type === "0") {
            select.attr('disabled', true);
        } else {
            $.ajax({
                url: location.href + '/get_trigers',
                method: 'post',
                data: {'triger_type': triger_type},
                success: function (response) {
                    select.empty();
                    select.append('<option value="0">Выберите триггер</option>');
                    for (let key in response) {
                        select.append('<option value="' + response[+key]["id"] + '">' + response[+key]["name"] + '</option>');
                    }
                    select.attr('disabled', false);
                },
                error: function (response) {

                }
            });
        }
    });

    $('#callback_triger_type_ed').on('change', function () {
        let triger_type = $(this).val();
        let select = $('#callback_triger_ed');
        $.ajax({
            url: location.href + '/get_trigers',
            method: 'post',
            data: {'triger_type': triger_type},
            success: function (response) {
                select.empty();
                select.append('<option value="0">Выберите триггер</option>');
                for (let key in response) {
                    select.append('<option value="' + response[+key]["id"] + '">' + response[+key]["name"] + '</option>');
                }
            },
            error: function (response) {

            }
        });
    });


    $('#create_callback').on('click', function () {
        let active;
        let name = $('#callback_name').val();
        let type = $('#callback_triger_type').val();
        let triger_id = $('#callback_triger').val();
        let triger_name = $('#callback_triger option:selected').text();
        let event = $('#callback_event').val();
        let pipeline_id = $('#callback_pipeline').val();
        let pipeline_name = $('#callback_pipeline option:selected').text();
        let status_id = $('#callback_status').val();
        let status_name = $('#callback_status option:selected').text();
        let task = $('#callback_task').val();
        let task_text = $('#callback_task_text').val();
        let note = $('#callback_note').val();
        if ($('#callback_activate').is(':checked')) {
            active = 1;
        } else {
            active = 0;
        }
        if (name === "") {
            showError("Введите название");
        } else if (type === "0") {
            showError("Выберите тип триггера");
        } else if ((triger_id === "0") || (triger_id === "")) {
            showError("Выберите триггер");
        } else if (event === "0") {
            showError("Выберите событие")
        } else if ((callback_event === 'note') && (callback_note === "")) {
            showError('Укажите текст примечания');
        } else if ((callback_event === 'task') && (callback_task === "0")) {
            showError('Выберите тип задачи');
        } else if ((callback_event === 'task') && (callback_task_text === "")) {
            showError('Введите текст задачи');
        } else if ((callback_event === 'status') && (callback_pipeline === "0")) {
            showError('Выберите воронку callback');
        } else if ((callback_event === 'status') && (callback_status === "0")) {
            showError('Выберите статус воронки callback');
        } else {
            $.ajax({
                url: location.href + '/add_callback',
                method: 'post',
                data: {
                    'name': name,
                    'type': type,
                    'triger_id': triger_id,
                    'triger_name': triger_name,
                    'event': event,
                    'pipeline_id': pipeline_id,
                    'pipeline_name': pipeline_name,
                    'status_id': status_id,
                    'status_name': status_name,
                    'task': task,
                    'task_text': task_text,
                    'note': note,
                    'is_active': active
                },
                success: function (response) {
                    console.log(response);
                    document.location.href = '/callbacks';
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    });


    $('.item_callback').on('click', function () {

        let callback = $(this).attr('callback');
        callback_id = callback;
        if (event.target.className === 'copy_url') {
            console.log('callback is ' + callback);
            //Копируем ссылку по нажатию кнопки
            let url = $('#callback_' + callback).attr('url');
            $('#hidden_url').val(url);
            $('#hidden_url').select();
            document.execCommand("copy");
            console.log($('#hidden_url').val());
            alert('Callback URL был скопирован')

        } else {
            $('#edit_callback_btn').trigger('click');
            $.ajax({
                url: location.href + '/open_callback',
                method: 'post',
                data: {'callback_id': callback},
                success: function (response) {
                    console.log(response);
                    loadData($('#callback_pipeline_ed'), null, null, null);
                    loadStatuses($('#callback_status_ed'), response['callback_info']['callback_pipeline']);
                    getTasks($('#callback_task_ed'));
                    $('#callback_name_ed').val(response['callback_info']['name']);
                    $('#callback_triger_type_ed').val(response['callback_info']['triger_type']);
                    $('#callback_triger_ed').empty();
                    $('#callback_triger_ed').append('<option value="0">Выберите триггер</option>');
                    for (let key in response['trigers']) {
                        $('#callback_triger_ed').append('<option value="' + response['trigers'][+key]["id"] + '">' + response['trigers'][+key]["name"] + '</option>');
                    }
                    $('#callback_triger_ed').val(response['callback_info']['triger_id']);
                    setTimeout(function () {
                        $('#callback_pipeline_ed').val(response['callback_info']['callback_pipeline']);
                        $('#callback_status_ed').val(response['callback_info']['callback_status']);
                        $('#callback_task_ed').val(response['callback_info']['callback_task']);
                    }, 1000);
                    $('#callback_note_ed').val(response['callback_info']['callback_note']);
                    $('#callback_task_text_ed').val(response['callback_info']['callback_task_text']);
                    let event = response['callback_info']['callback_event'];
                    $('#callback_event_ed').val(event);
                    if (event === 'status') {
                        $('.callback_statuses').show();
                        $('.callback_type_task').hide();
                        $('.callback_note_text').hide();
                    } else if (event === 'task') {
                        $('.callback_statuses').hide();
                        $('.callback_note_text').hide();
                        $('.callback_type_task').show();
                        getTasks($('#callback_task'));

                    } else if (event === 'note') {
                        $('.callback_statuses').hide();
                        $('.callback_note_text').show();
                        $('.callback_type_task').hide();
                    } else {
                        $('.callback_statuses').hide();
                        $('.callback_note_text').hide();
                        $('.callback_type_task').hide();
                    }
                    if (response['callback_info']['is_active'] === 1) {
                        $('#callback_activate_ed').prop('checked', true);
                    } else {
                        $('#callback_activate_ed').prop('checked', false);
                    }

                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    });


    $('#update_callback').on('click', function () {
        let name = $('#callback_name_ed').val();
        let type = $('#callback_triger_type_ed').val();
        let triger_id = $('#callback_triger_ed').val();
        let triger_name = $('#callback_triger_ed option:selected').text();
        let event = $('#callback_event_ed').val();
        let pipeline_id = $('#callback_pipeline_ed').val();
        let pipeline_name = $('#callback_pipeline_ed option:selected').text();
        let status_id = $('#callback_status_ed').val();
        let status_name = $('#callback_status_ed option:selected').text();
        let task = $('#callback_task_ed').val();
        let task_text = $('#callback_task_text_ed').val();
        let note = $('#callback_note_ed').val();
        let is_active;
        if ($('#callback_activate_ed').is(':checked')) {
            is_active = 1;
        } else {
            is_active = 0;
        }

        if (name === "") {
            showError("Введите название");
        } else if (type === "0") {
            showError("Выберите тип триггера");
        } else if ((triger_id === "0") || (triger_id === "")) {
            showError("Выберите триггер");
        } else if (event === "0") {
            showError("Выберите событие")
        } else if ((callback_event === 'note') && (callback_note === "")) {
            showError('Укажите текст примечания');
        } else if ((callback_event === 'task') && (callback_task === "0")) {
            showError('Выберите тип задачи');
        } else if ((callback_event === 'task') && (callback_task_text === "")) {
            showError('Введите текст задачи');
        } else if ((callback_event === 'status') && (callback_pipeline === "0")) {
            showError('Выберите воронку callback');
        } else if ((callback_event === 'status') && (callback_status === "0")) {
            showError('Выберите статус воронки callback');
        } else {

            $.ajax({
                url: location.href + '/update_callback',
                method: 'post',
                data: {
                    'id': callback_id,
                    'name': name,
                    'type': type,
                    'triger_id': triger_id,
                    'triger_name': triger_name,
                    'event': event,
                    'pipeline_id': pipeline_id,
                    'pipeline_name': pipeline_name,
                    'status_id': status_id,
                    'status_name': status_name,
                    'task': task,
                    'task_text': task_text,
                    'note': note,
                    'is_active': is_active
                },
                success: function (response) {
                    console.log(response);
                    document.location.href = '/callbacks';
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    });

    $('.delete_btn').on('click', function () {
        $.ajax({
            url: location.href + '/remove_callback',
            method: 'post',
            data: {'id': callback_id},
            success: function (response) {
                document.location.href = '/callbacks';
            },
            error: function (response) {
                console.log(response);
            }
        });
    });

    $('#create_callback_btn').on('click', function () {
        $('.callback_statuses').hide();
        $('.callback_type_task').hide();
        $('.callback_note_text').hide();
        $('#callback_name').val('');
        $('#callback_triger_type').val('0');
        $('#callback_triger').empty();
        $('#callback_triger').attr('disabled', true);
        $('#callback_event').val('0');
    });

    var emojiPicker = new EmojiPicker({
      emojiable_selector: '[data-emojiable=true]',
      assetsPath: '/lib/emoji-picker-main/lib/img/',
      popupButtonClasses: 'fa fa-smile-o',
      iconSize: 20
    });
    emojiPicker.discover();
});
