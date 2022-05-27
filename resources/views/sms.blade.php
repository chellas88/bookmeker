@extends('layouts.home')

@section("head")
    <title>СМС рассылка</title>
@endsection

@section("content")
    <div class="container-fluid">
        <div class="row content">
            @include('layouts.sidebarMenu')

            <div class="col-10">
                <button class="btn btn-warning" id="open_audio_triger_popup" data-toggle="modal" data-target="#new_triger">Добавить триггер
                </button>
                <button class="btn btn-warning" id="edit_triger_zvonobot" data-toggle="modal" data-target="#edit_triger"></button>
                <input type="text" id="hidden_url">
                <table class="triger_list table table-bordered table-middle">
                  <thead class="thead-dark-blue">
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Тип события</th>
                        <th>Обработка</th>
                        <th>Воронка</th>
                        <th>Статус</th>
                        <th>Текст SMS</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(!empty($data))
                        @foreach($data as $triger)
                            @if (empty($triger['send_sms']))

                            @endif
                            <tr triger="{{$triger['id']}}" class="item_triger
                                @if($triger['is_active'] == 1)
                                is_active
@else
                                is_deactive
@endif
                                ">

                                <td align="center">{{$triger['id']}}</td>
                                <td>{{$triger['name']}}</td>
                                <td>
                                    @if ($triger['event'] == 'status')
                                        Изменение статуса
                                    @elseif ($triger['event'] == 'add')
                                        Создание лида
                                    @endif
                                </td>
                                <td>{{$triger['delay_type']}}</td>
                                <td>{{$triger['pipeline_name']}}</td>
                                <td>{{$triger['status_name']}}</td>
                                <td>{{$triger['text']}}</td>

                            </tr>
                        @endforeach

                    @else
                        <tr>
                            <td colspan="8">Нет записей</td>
                        </tr>
                    @endif
                  <tbody>

                </table>


                <div id="new_triger" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Новый триггер</h2>
                            <table>
                                <tr>
                                    <td>Название триггера</td>
                                    <td colspan="2"><input type="text" id="audio_triger_name" class="form-control "></td>
                                </tr>
                                <tr>
                                    <td>Событие</td>
                                    <td colspan="2">
                                        <select id="audio_event" class="form-control ">
                                            <option value="0">Выберите событие</option>
                                            <option value="add">Создание лида</option>
                                            <option value="status">Изменение статуса</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>Срабатывать</td>
                                    <td colspan="2">
                                        <select id="gotoevent" class="form-control ">
                                            <option value="0">Сразу</option>
                                            <option value="1">Через промежуток времени</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr id="delay">
                                    <td>Задержка</td>
                                    <td><input type="text" id="audio_delay_time" class="form-control "></td>
                                    <td><select id="audio_delay_type" class="form-control ">
                                            <option value="minutes">минут</option>
                                            <option value="hours">часов</option>
                                            <option value="days">дней</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>Отправитель</td>
                                    <td colspan="2">
                                        <select id="sender" class="form-control ">
                                            <option>Выберите отправителя</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Воронка амо</td>
                                    <td colspan="2">
                                        <select id="audio_pipeline" class="form-control ">
                                            <option>Выберите воронку</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Статус</td>
                                    <td colspan="2">
                                        <select disabled id="audio_pipeline_status" class="form-control ">
                                            <option>Выберите статус</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Текст сообщения</td>
                                    <td colspan="2"><input type="text" id="sms_body" class="form-control "></td>
                                </tr>
                                <tr>
                                    <td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст SMS вставьте тег {url} в текст SMS</div></td>
                                </tr>

                                <tr>
                                    <td>Ссылка</td>
                                    <td colspan="2"><input type="text" id="sms_url" class="form-control "></td>
                                </tr>


                                <tr>
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" id="audio_triger_activate"><label
                                            for="audio_triger_activate">&nbsp;Активировать</label></td>
                                </tr>
                                <tr><td colspan="3" class="error_msg"></td></tr>


                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="sms_triger_add_btn" class="add btn btn-success">Добавить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>


                <div id="edit_triger" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Триггер</h2>
                            <table>
                                <tr>
                                    <td>Название триггера</td>
                                    <td colspan="2"><input type="text" id="audio_triger_name_ed" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Событие</td>
                                    <td colspan="2">
                                        <select id="audio_event_ed" class="form-control ">
                                            <option value="0">Выберите событие</option>
                                            <option value="add">Создание лида</option>
                                            <option value="status">Изменение статуса</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>Срабатывать</td>
                                    <td colspan="2">
                                        <select id="gotoevent_ed" class="form-control ">
                                            <option value="0">Сразу</option>
                                            <option value="1">Через промежуток времени</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr id="delay_ed">
                                    <td>Задержка</td>
                                    <td><input type="text" id="audio_delay_time_ed" class="form-control "></td>
                                    <td><select id="audio_delay_type_ed" class="form-control ">
                                            <option value="minutes">минут</option>
                                            <option value="hours">часов</option>
                                            <option value="days">дней</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>Отправитель</td>
                                    <td colspan="2">
                                        <select id="sender_ed" class="form-control ">
                                            <option>Выберите отправителя</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Воронка амо</td>
                                    <td colspan="2">
                                        <select id="audio_pipeline_ed" class="form-control ">

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Статус</td>
                                    <td colspan="2">
                                        <select id="audio_pipeline_status_ed" class="form-control ">

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Текст сообщения</td>
                                    <td colspan="2"><input type="checkbox" id="send_sms_ed" style="display: none;"><input type="text" id="sms_body_ed" placeholder="Текст сообщения" class="form-control "></td>
                                </tr>
                                <tr><td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст SMS вставьте тег {url} в текст SMS</div></td></tr>

                                <tr>
                                    <td>Ссылка</td>
                                    <td colspan="2"><input type="text" id="sms_url_ed" class="form-control "></td>
                                </tr>

                                <tr>
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" id="audio_triger_activate_ed"><label
                                            for="audio_triger_activate_ed">&nbsp;Активировать</label></td>
                                </tr>
                                <tr><td colspan="3" class="error_msg"></td></tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="audio_triger_update_btn" class="add btn btn-success">Изменить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>



                <div id="add_audio" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Добавить аудиозапись</h2>
                            <table>
                                <tr>
                                    <td>Название</td>
                                    <td><input type="text" id="audio_name" class="form-control "></td>
                                </tr>
                                <tr>
                                    <td>ID</td>
                                    <td><input type="text" id="audio_id" class="form-control "></td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <button id="audio_add_btn" class="add btn btn-success">Добавить</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
