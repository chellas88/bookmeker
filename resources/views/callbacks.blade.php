@extends('layouts.home')

@section("head")
    <title>Callbacks</title>
@endsection

@section("content")
    <div class="container-fluid">
        <div class="row content">
            @include('layouts.sidebarMenu')

            <div class="col-10">
                <button class="btn btn-warning" id="create_callback_btn" data-toggle="modal" data-target="#new_callback">Добавить callback
                </button>
                <button class="btn btn-warning" id="edit_callback_btn" data-toggle="modal" data-target="#edit_callback"></button>
                <input type="text" id="hidden_url">
                <table class="triger_list table table-bordered table-middle">
                  <thead class="thead-dark-blue">
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Тип триггера</th>
                        <th>Название триггера</th>
                        <th>Событие</th>
                        <th>Callback URL</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(!empty($data))
                        @foreach($data as $callback)
                            <tr callback="{{$callback['id']}}" class="item_callback
                                @if($callback['is_active'] == 1)
                                is_active
@else
                                is_deactive
@endif
                                ">

                                <td align="center">{{$callback['id']}}</td>
                                <td>{{$callback['name']}}</td>
                                <td>
                                    @if($callback['triger_type'] == 'zvonobot')
                                        Звонобот
                                    @elseif ($callback['triger_type'] == 'sms')
                                        SMS
                                    @endif
                                </td>
                                <td>{{$callback['triger_name']}}</td>
                                <td>
                                    @if ($callback['callback_event'] == 'task')
                                        Создание задачи
                                    @elseif ($callback['callback_event'] == 'note')
                                        Добавление примечания
                                    @elseif ($callback['callback_event'] == 'status')
                                        Изменение статуса
                                    @endif
                                </td>
                                <td align="center">
                                        <button name="copy_url" id="callback_{{$callback['id']}}" class="copy_url btn btn-outline-dark url="{{$callback['callback_url']}}">Копировать URL</button>
                                </td>
                            </tr>
                        @endforeach

                    @else
                        <tr>
                            <td colspan="8">Нет записей</td>
                        </tr>
                    @endif
                  <tbody>


                </table>


                <div id="new_callback" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Новый callback</h2>
                            <table>
                                <tr>
                                    <td width="150px">Название</td>
                                    <td colspan="2"><input type="text" id="callback_name" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Тип триггера</td>
                                    <td callspan="2">
                                        <select id="callback_triger_type" class="form-control">
                                            <option value="0">Выберите тип триггера</option>
                                            <option value="zvonobot">Звонобот</option>
                                            <option value="sms">SMS</option>
                                        </select>
                                    </td>

                                </tr>

                                <tr>
                                    <td>Триггер</td>
                                    <td colspan="2"><select disabled id="callback_triger" class="form-control"></select></td>
                                </tr>

                                <tr>
                                    <td>Событие</td>
                                    <td colspan="2">
                                        <select id="callback_event" class="form-control">
                                            <option value="0">Выберите событие</option>
                                            <option value="status">Изменить статус</option>
                                            <option value="task">Создать задачу</option>
                                            <option value="note">Добавить примечание</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="callback_statuses">
                                    <td>Воронка</td>
                                    <td colspan="2">
                                        <select id="callback_pipeline" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_statuses">
                                    <td>Статус</td>
                                    <td colspan="2">
                                        <select id="callback_status" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_type_task">
                                    <td>Тип задачи</td>
                                    <td colspan="2">
                                        <select id="callback_task" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_type_task">
                                    <td>Текст задачи</td>
                                    <td colspan="2">
                                        <input type="text" id="callback_task_text" class="form-control">
                                    </td>
                                </tr>
                                <tr class="callback_note_text">
                                    <td>Текст примечания</td>
                                    <td colspan="2">
                                        <input type="text" id="callback_note" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" id="callback_activate"><label
                                            for="callback_activate">&nbsp;Активировать</label></td>
                                </tr>

                                <tr><td colspan="3" class="error_msg"></td></tr>


                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="create_callback" class="add btn btn-success">Добавить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>


                <div id="edit_callback" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Изменить callback</h2>
                            <table>
                                <tr>
                                    <td>Название</td>
                                    <td colspan="2"><input type="text" id="callback_name_ed" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Тип триггера</td>
                                    <td callspan="2">
                                        <select id="callback_triger_type_ed" class="form-control">
                                            <option value="0">Выберите тип триггера</option>
                                            <option value="zvonobot">Звонобот</option>
                                            <option value="sms">SMS</option>
                                        </select>
                                    </td>

                                </tr>

                                <tr>
                                    <td>Триггер</td>
                                    <td colspan="2"><select id="callback_triger_ed" class="form-control"></select></td>
                                </tr>

                                <tr>
                                    <td>Событие</td>
                                    <td colspan="2">
                                        <select id="callback_event_ed" class="form-control">
                                            <option value="0">Выберите событие</option>
                                            <option value="status">Изменить статус</option>
                                            <option value="task">Создать задачу</option>
                                            <option value="note">Добавить примечание</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="callback_statuses">
                                    <td>Воронка</td>
                                    <td colspan="2">
                                        <select id="callback_pipeline_ed" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_statuses">
                                    <td>Статус</td>
                                    <td colspan="2">
                                        <select id="callback_status_ed" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_type_task">
                                    <td>Тип задачи</td>
                                    <td colspan="2">
                                        <select id="callback_task_ed" class="form-control"></select>
                                    </td>
                                </tr>
                                <tr class="callback_type_task">
                                    <td>Текст задачи</td>
                                    <td colspan="2">
                                        <input type="text" id="callback_task_text_ed" class="form-control">
                                    </td>
                                </tr>
                                <tr class="callback_note_text">
                                    <td>Текст примечания</td>
                                    <td colspan="2">
                                        <input type="text" id="callback_note_ed" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" id="callback_activate_ed"><label
                                            for="callback_activate_ed">&nbsp;Активировать</label></td>
                                </tr>

                                <tr><td colspan="3" class="error_msg"></td></tr>

                                <tr><td colspan="3" align="center"><button class="delete_btn">Удалить Callback</button></td> </tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="update_callback" class="add btn btn-success">Сохранить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
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
