@extends('layouts.home')

@section("head")
    <title>Массовые действия</title>
@endsection

@section("content")
    <div class="container-fluid">
        <div class="row content">
        @include('layouts.sidebarMenu')

            <div class="col-10">
                <div class="settings">
                    К-во записей на странице
                    <select id="leads_count" class="leads_count form-control form-control-sm">
                        <option value="all">Показывать все</option>
                        <option selected value="50">50</option>
                        <option value="100">100</option>
                        <option value="250">250</option>
                    </select>

                </div>
                <div class="filtr">
                    <div class="show_filtr"><a id="show_filtr" href="#">Показать фильтр</a></div>
                    <div class="filtr_content">
                        <table>
                            <tr>
                                <td>Период создания лидов</td>
                                <td><input type="date" class="filtr_input_date form-control form-control-sm" id="filtr_created_from"></td>
                                <td><input type="date" id="filtr_created_to" class="filtr_input_date form-control form-control-sm"></td>
                                <td><button id="filtr_created_reset" class="btn btn-light btn-outline-dark btn-sm">Сбросить</button></td>
                            </tr>
                        <tr>
                            <td>Период изменения лидов</td>
                            <td><input type="date" id="filtr_updated_from" class="filtr_input_date form-control form-control-sm"></td>
                            <td><input type="date" id="filtr_updated_to" class="filtr_input_date form-control form-control-sm"></td>
                            <td><button id="filtr_updated_reset" class="btn btn-light btn-outline-dark btn-sm">Сбросить</button></td>
                        </tr>
                            <tr>
                                <td>Воронка AmoCRM</td><td colspan="3"><select class="filtr_pipeline form-control form-control-sm"></select></td>
                            </tr>
                            <tr><td colspan="4"><div class="filtr_status"></div></td></tr>
                            <tr><td colspan="4">
                              <button id="filtr_add" class="btn btn-primary btn-sm">Фильтровать</button>
                              <button id="filtr_reset" class="btn btn-light btn-outline-dark btn-sm">Сбросить</button>
                            </td></tr>
                        </table>
                    </div>

                </div>

                <div class="loader"><img src="{{asset('img/loader.svg')}}"></div>
                <div class="page_content">
                    <div class="leads_menu">
                        <a href="#" id="select_all">Отметить все</a>
                        <a href="#" id="unselect_all">Снять выделение</a>
                        <a href="#" id="autocalling" data-toggle="modal" data-target="#autoBotSender_popup" class="btn" >Настройка Бот авторассылки</a>
                        <a href="#" id="autocalling" data-toggle="modal" data-target="#autocaller_popup" class="btn">Настройки автопрозвона</a>
                        <a href="#" id="autosender" data-toggle="modal" data-target="#autosender_popup" class="btn">Настройка SMS авторассылки</a>
                    </div>
                    <div class="leads_nav">
                        <a class="disabled" href="#" id="prev"><< Предыдущая страница</a>
                        <a id="current_page" page="1">1</a>
                        <a href="#" id="next" class="disabled">Следующая страница >></a>
                    </div>
                    <div class="leads">
                      <h3 class="settings_filter_text">Настройте фильтр</h3>
                      <table class="global_leads_table table table-bordered table-striped table-middle" style="display:none;">
                        <thead class="thead-dark-blue">
                          <tr>
                              <th class="checkbox_lead">#</th>
                              <th class="title_lead">Название сделки</th>
                              <th>Воронка</th>
                              <th>Этап</th>
                              <th>Дата создания</th>
                              <th>Дата изменения</th>
                              <th>Ссылка</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                    </div>
                </div>




                <div id="autosender_popup" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Настройка SMS авторассылки</h2>
                            <table>
                                <tr>
                                    <td>Название рассылки</td>
                                    <td><input type="text" id="name" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Отправитель</td>
                                    <td colspan="2">
                                        <select id="sender" class="form-control">
                                            <option>Выберите отправителя</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Текст сообщения</td>
                                    <td colspan="2"><input type="text" id="sms_body" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст SMS вставьте тег {url} в текст SMS</div></td>
                                </tr>

                                <tr>
                                    <td>Ссылка</td>
                                    <td colspan="2"><input type="text" id="sms_url" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Параметры рассылки</td>
                                    <td colspan="2">
                                        <p><input checked type="radio" name="select" id="selected_leads" value="selected"><label for="selected_leads">&nbsp;Рассылать отмеченным</label></p>
                                        <p><input type="radio" name="select" id="all_leads" value="all"><label for="all_leads">&nbsp;Рассылать всем</label></p>
                                    </td>
                                </tr>


                                <tr><td colspan="3" class="error_msg"></td></tr>


                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="add_autosender" class="add btn btn-success">Добавить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>


                <div id="autocaller_popup" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Настройки автопрозвона</h2>
                            <form class="form_autocaller">
                              <table>
                                  <tr>
                                      <td>Название прозвона</td>
                                      <td colspan="2"><input type="text" id="name_z" name="name" class="form-control"></td>
                                  </tr>



                                  <tr>
                                      <td>Аудиоролик</td>
                                      <td colspan="2">
                                          <select id="track_z" name="audio" class="form-control">
                                              <option value="0">Выберите аудиоролик</option>
                                          </select>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td colspan="2"><input type="checkbox" id="ivr_show" name="is_ivr"><label for="ivr_show">&nbsp;Добавить IVR</label></td>
                                  </tr>

                                  <tr  class="ivr_block">
                                      <td>Цифра нажатия</td>
                                      <td colspan="2"><input type="text" id="press_digit_z" name="digit" class="form-control"></td>
                                  </tr>
                                  <tr  class="ivr_block">
                                      <td></td>
                                      <td colspan="2"><input type="checkbox" id="add_track" name="is_audio"><label for="add_track">&nbsp;Добавить аудио</label></td>
                                  </tr>

                                  <tr class="additional_audio">
                                      <td>Дополнительный аудиоролик</td>
                                      <td colspan="2">
                                          <select id="ex_audio_track_z" name="sec_audio" class="form-control">
                                              <option value="0">Выберите дополнительный аудиоролик</option>
                                          </select>
                                      </td>
                                  </tr>
                                  <tr  class="ivr_block">
                                      <td></td>
                                      <td colspan="2"><input type="checkbox" id="send_sms" name="is_sms"><label for="send_sms">&nbsp;Отправить
                                              SMS</label></td></tr>
                                  <tr class="sms_block">
                                      <td>Отправитель</td>
                                      <td colspan="2">
                                          <select id="sender_z" name="sender_id" class="form-control">
                                              <option disabled>Выберите отправителя</option>
                                          </select>
                                      </td>
                                  </tr>
                                  <tr class="sms_block">
                                      <td>Текст сообщения</td>
                                      <td colspan="2"><input type="text" id="sms_body_z" name="sms_text" class="form-control"></td>
                                  </tr>
                                  <tr class="sms_block"><td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст SMS вставьте тег {url} в текст SMS</div></td></tr>

                                  <tr class="sms_block">
                                      <td>Ссылка</td>
                                      <td colspan="2"><input type="text" id="sms_url_z" name="sms_url" class="form-control"></td>
                                  </tr>

                                  <tr>
                                      <td>Параметры прозвона</td>
                                      <td colspan="2">
                                          <p><input checked type="radio" name="destination" id="selected_leads" value="list"><label for="selected_leads">&nbsp;Прозвон отмеченным</label></p>
                                          <p><input type="radio" name="destination" id="all_leads" value="all"><label for="all_leads">&nbsp;Прозвон всем</label></p>
                                      </td>
                                  </tr>


                                  <tr><td colspan="3" class="error_msg"></td></tr>
                                  <tr>
                                      <td colspan="3" align="center">
                                          <button id="add_autocaller" class="add btn btn-success">Запустить прозвон</button>
                                          <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                      </td>
                                  </tr>
                              </table>
                            </form>
                        </div>
                    </div>
                </div>


                <div id="autoBotSender_popup" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2>Настройка Бот авторассылки</h2>
                            <form class="autoBotSender_form" action="" method="post">

                                <table>
                                <tr>
                                    <td>Название рассылки</td>
                                    <td><input type="text" name="name" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Мессенджер</td>
                                    <td colspan="2">
                                        <select name="driver" class="form-control">
                                            <option value="0">Выберите мессенджер</option>
                                            <option value="all">Все</option>
                                            <option value="Viber">Viber</option>
                                            <option value="Telegram">Telegram</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Сообщение</td>
                                    <td colspan="2">
                                      <select name="type_message" class="type_message form-control">
                                          <option value="0">Выберите тип сообщения</option>
                                          <option value="template">Шаблон сообщения</option>
                                          <option value="new_message">Новое сообщение</option>
                                      </select>

                                    </td>
                                </tr>
                                <tr class="template_wrap">
                                    <td>Шаблон сообщения</td>
                                    <td colspan="2">
                                      <select name="template" class="template form-control"></select>
                                    </td>
                                </tr>
                                <tr class="new_message_wrap">
                                    <td>Текст сообщения</td>
                                    <td colspan="2">
                                      <p class="lead emoji-picker-container">
                                      <textarea rows="5"  name="text_message" data-emojiable="true"
                                      {{-- data-emoji-input="unicode" --}}
                                      ></textarea>
                                      </p>

                                    </td>
                                </tr>
                                <tr class="new_message_wrap">
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" name="is_link" id="link_show"><label for="link_show">&nbsp;Добавить ссылку</label></td>
                                </tr>
                                <tr class="link_block">
                                    <td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст сообщения вставьте тег {url} в текст сообщения</div></td>
                                </tr>

                                <tr class="link_block">
                                    <td>Ссылка</td>
                                    <td colspan="2"><input type="text" name="link" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Параметры рассылки</td>
                                    <td colspan="2">
                                        <p><input checked type="radio" name="destination"  value="list"><label for="selected_leads">&nbsp;Рассылать отмеченным</label></p>
                                        <p><input type="radio" name="destination"  value="all"><label for="all_leads">&nbsp;Рассылать всем</label></p>


                                    </td>
                                </tr>


                                <tr><td colspan="3" class="error_msg"></td></tr>


                                <tr>
                                    <td colspan="3" align="center">
                                        <button id="add_autosender" class="add btn btn-success">Добавить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                            </form>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
@endsection
