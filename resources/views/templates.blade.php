@extends('layouts.home')

@section("head")
    <title>Шаблоны</title>
@endsection

@section("content")
    <div class="container-fluid">
        <div class="row content">
            @include('layouts.sidebarMenu')

            <div class="col-10">
                <button class="btn btn-warning" id="create_template_btn" data-toggle="modal" data-target="#template_popup">Добавить шаблон
                </button>
                <input type="text" id="hidden_url">
                <table class="templates_list table table-bordered table-striped table-hover table-middle">
                  <thead class="thead-dark-blue">
                    <tr>
                        <th class="center">ID</th>
                        <th>Название</th>
                        <th>Сообщение</th>
                        <th>URL</th>
                        <th class="buttons_col"></th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(!empty($data))
                        @foreach($data as $template)
                            <tr id="{{$template['id']}}" name="{{$template['name']}}" class="item_template">

                                <td class="center">{{$template['id']}}</td>
                                <td>{{$template['name']}}</td>
                                <td>{{iconv_substr ($template['message'], 0 , 150 , "UTF-8" )}}</td>
                                <td>{{$template['url']}}</td>
                                <td class="center buttons_col"><button class="btn btn-secondary delete_template" title="Удалить"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach

                    @else
                        <tr>
                            <td colspan="5">Нет записей</td>
                        </tr>
                    @endif
                  <tbody>

                </table>

                <div id="template_popup" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <h2 class="title"></h2>
                            <form class="template_form popup_form" action="" method="post">
                                <input type="hidden" name="action">
                                <input type="hidden" name="id">
                                <table>
                                <tr>
                                    <td>Название рассылки</td>
                                    <td><input type="text" name="name" class="form-control form-control-sm"></td>
                                </tr>
                                <tr>
                                    <td>Текст сообщения</td>
                                    <td colspan="2">
                                      <p class="lead emoji-picker-container">
                                      <textarea rows="5"  name="text_message" data-emojiable="true"
                                      {{-- data-emoji-input="unicode" --}}
                                      ></textarea>
                                      </p>

                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2"><input type="checkbox" name="is_url" id="link_show"><label for="link_show">&nbsp;Добавить ссылку</label></td>
                                </tr>
                                <tr class="link_block">
                                    <td align="center" colspan="3"><div id="insert_url">Чтобы вставить ссылку из поля ниже в текст сообщения вставьте тег {url} в текст сообщения</div></td>
                                </tr>

                                <tr class="link_block">
                                    <td>Ссылка</td>
                                    <td colspan="2"><input type="text" name="url"></td>
                                </tr>


                                <tr><td colspan="3" class="error_msg"></td></tr>


                                <tr>
                                    <td colspan="3" align="center">
                                        <button class="add btn btn-success">Добавить</button>
                                        <button data-dismiss="modal" class="btn btn-secondary">Отменить</button>
                                    </td>
                                </tr>
                            </table>
                            </form>
                        </div>
                    </div>
                </div>


                <div id="delete_popup" class="modal" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Удаление</h5>
                        {{-- <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button> --}}
                      </div>
                      <div class="modal-body">
                        <p>Вы действительно хотите удалить шаблон <strong>"<span class="name_template"></span>"</strong> <span class="id_template_color">(id = <span class="id_template"></span>)</span>?</p>
                      </div>
                      <div class="modal-footer">
                        <button id="" type="button" class="btn btn-danger delete_template_btn">Удалить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отменить</button>
                      </div>
                    </div>
                  </div>
                </div>


            </div>
        </div>
    </div>
@endsection
