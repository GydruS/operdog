<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../../AbstractModules/AbstractDictionary/AbstractDictionary.xsl"/>
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template name="newAdvertForm">
    <h1>Разместить объявление</h1>
    <form  role="form" class="form-horizontal" id="addAdvertForm" action="/{$siteRoot}{/document/dictionaryInfo/moduleName}/new" method="post" enctype="multipart/form-data" accept-charset="utf-8">
        <input type="hidden" name="item[id]" value="{/document/item/id}"/>
        <xsl:choose>
            <xsl:when test="$MOBILE = '1'">            
                <xsl:call-template name="advertFormFieldsMobile"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="advertFormFields"/>
            </xsl:otherwise>
        </xsl:choose>
    </form>
</xsl:template>

<xsl:template name="advertFormFields">
    <xsl:variable name="edit" select="/document/item/id &gt; 0"/>
    <div class="form-group">
        <div class="row">
            <!--<label class="control-label col-xs-3">Заголовок</label>
            <div class="col-xs-7">
                <input type="text" id="name" class="form-control" name="item[name]" value="{/document/item/name}" style="width: 100%;"/>
            </div>-->
            <label class="control-label col-xs-3">Пропал(а)</label>
            <div class="col-xs-7">
                <select id="categoryId" class="form-control" name="item[categoryId]" style="width: 100%;">
                    <option value="0">[Выберите]</option>
                    <xsl:for-each select="/document/categories/category">
                        <option value="{id}">
                            <xsl:if test="/document/item/categoryId = id">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <xsl:value-of select="name"/>
                        </option>
                    </xsl:for-each>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Кличка</label>
            <div class="col-xs-7">
                <input type="text" id="nickname" class="form-control" name="item[nickname]" value="{/document/item/nickname}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Пол</label>
            <div class="col-xs-7">
                <input type="radio" name="item[gender]" value="Male" id="Male" class="">
                    <xsl:if test="item/gender = 'Male'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Male" style="font-weight: normal;" class="pointer">&#160;Мужской</label>
                &#160;&#160;&#160;
                <input type="radio" name="item[gender]" value="Female" id="Female" class="">
                    <xsl:if test="item/gender = 'Female'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Female" style="font-weight: normal;" class="pointer">&#160;Женский</label>
            </div>
        </div>
    </div>
    <!--<div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Ориентировочное место пропажи</label>
            <div class="col-xs-7">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[lastSeenPlace]" value="{/document/item/lastSeenPlace}" style="width: 100%;"/>
            </div>
        </div>
    </div>-->
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Город</label>
            <div class="col-xs-2">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[city]" value="{/document/item/city}" style="width: 100%;"/>
            </div>
            <label class="control-label col-xs-1">Адрес</label>
            <div class="col-xs-4">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[lastSeenPlace]" value="{/document/item/lastSeenPlace}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Дата пропажи</label>
            <div class="col-xs-3">
                <div class="input-group date" id="eventDate">
                    <input class="form-control" type="text" name="item[eventDate]" value="{/document/item/eventDate}"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
            </div>
            <label class="control-label col-xs-2">Время пропажи</label>
            <div class="col-xs-2">
                <div class="input-group clockpicker" data-autoclose="true">
                    <input type="text" class="form-control" name="item[eventTime]" value="{/document/item/eventTime}" id="eventTime"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
                </div>
            </div>
        </div>
    </div>
    <xsl:call-template name="formHR"/>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Порода</label>
            <div class="col-xs-4">
                <input type="text" id="breed" class="form-control" name="item[breed]" value="{/document/item/breed}" style="width: 100%;"/>
            </div>
            <label class="control-label col-xs-1">Окрас</label>
            <div class="col-xs-2">
                <input type="text" id="color" class="form-control" name="item[color]" value="{/document/item/color}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Глаза</label>
            <div class="col-xs-1">
                <input type="text" id="eyes" class="form-control" name="item[eyes]" value="{/document/item/eyes}" style="width: 100%;"/>
            </div>
            <label class="control-label col-xs-1">Уши</label>
            <div class="col-xs-1">
                <input type="text" id="ears" class="form-control" name="item[ears]" value="{/document/item/ears}" style="width: 100%;"/>
            </div>
            <label class="control-label col-xs-1">Хвост</label>
            <div class="col-xs-1">
                <input type="text" id="tail" class="form-control" name="item[tail]" value="{/document/item/tail}" style="width: 100%;"/>
            </div>
            <label class="control-label col-xs-1">Возраст</label>
            <div class="col-xs-1">
                <input type="text" id="age" class="form-control" name="item[age]" value="{/document/item/age}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Особые приметы</label>
            <div class="col-xs-7">
                <input type="text" id="specialSigns" class="form-control" name="item[specialSigns]" value="{/document/item/specialSigns}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Обстоятельства пропажи</label>
            <div class="col-xs-7">
                <textarea id="text" class="form-control" name="item[text]" style="width: 100%;" rows="5"><xsl:value-of select="/document/item/text" disable-output-escaping="yes"/></textarea>
            </div>
        </div>
    </div>
    <xsl:call-template name="formHR"/>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Телефон</label>
            <div class="col-xs-7">
                <input type="text" id="phone" class="form-control" name="item[phone]" value="{/document/item/phone}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <!--<div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Контактное лицо</label>
            <div class="col-xs-7">
                <input type="text" id="contacterName" class="form-control" name="item[contacterName]" value="{/document/item/contacterName}" style="width: 100%;"/>
            </div>
        </div>
    </div>-->
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Вознагражение</label>
            <div class="col-xs-7">
                <input type="text" id="reward" class="form-control" name="item[reward]" value="{/document/item/reward}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
        <label class="col-sm-3 control-label">Фотография</label>
            <div class="col-sm-7">
                <div class="input-group">
                    <label class="input-group-btn">
                        <span class="btn btn-default">
                            Обзор… <input type="file" name="itemImages[]" style="display: none;" multiple="false"/>
                        </span>
                    </label>
                    <input id="fileName" type="text" class="form-control" readonly="1"/>
                </div>
            </div>
        </div>
    </div>
    <xsl:if test="/document/action != 'new'">
        <div class="form-group">
            <div class="row">
                <label class="col-sm-3 control-label">Загруженные фото</label>
                <div class="col-sm-7">
                    <xsl:call-template name="imagesForm"/>
                </div>
            </div>
        </div>
    </xsl:if>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3"></label>
            <div class="col-xs-7">
                <xsl:variable name="btnCaption">
                    <xsl:choose>
                        <xsl:when test="$edit">Сохранить</xsl:when>
                        <xsl:otherwise>Разместить</xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <input type="submit" class="btn btn-primary" value="{$btnCaption}"/>
                <xsl:if test="$edit">
                    &#160;<a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/delete/{/document/item/id}" class="btn btn-default" onclick="return deleteRecord({/document/item/id});">
                        Удалить объявление
                    </a>
                </xsl:if>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template name="advertFormFieldsMobile">
    <xsl:variable name="edit" select="/document/item/id &gt; 0"/>
    <div class="form-group">
        <div class="row">
            <!--<label class="control-label col-xs-3">Заголовок</label>
            <div class="col-xs-7">
                <input type="text" id="name" class="form-control" name="item[name]" value="{/document/item/name}" style="width: 100%;"/>
            </div>-->
            <label class="control-label col-xs-5">Пропал(а)</label>
            <div class="col-xs-7">
                <select id="categoryId" class="form-control" name="item[categoryId]">
                    <option value="0">[Выберите]</option>
                    <xsl:for-each select="/document/categories/category">
                        <option value="{id}">
                            <xsl:if test="/document/item/categoryId = id">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <xsl:value-of select="name"/>
                        </option>
                    </xsl:for-each>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Кличка</label>
            <div class="col-xs-7">
                <input type="text" id="nickname" class="form-control" name="item[nickname]" value="{/document/item/nickname}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Пол</label>
            <div class="col-xs-7">
                <input type="radio" name="item[gender]" value="Male" id="Male" class="">
                    <xsl:if test="item/gender = 'Male'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Male" style="font-weight: normal;" class="pointer">&#160;Мужской</label>
                &#160;&#160;&#160;
                <input type="radio" name="item[gender]" value="Female" id="Female" class="">
                    <xsl:if test="item/gender = 'Female'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Female" style="font-weight: normal;" class="pointer">&#160;Женский</label>
            </div>
        </div>
    </div>
    <!--<div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Ориентировочное место пропажи</label>
            <div class="col-xs-7">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[lastSeenPlace]" value="{/document/item/lastSeenPlace}" style="width: 100%;"/>
            </div>
        </div>
    </div>-->
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Город</label>
            <div class="col-xs-7">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[city]" value="{/document/item/city}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Адрес</label>
            <div class="col-xs-7">
                <input type="text" id="lastSeenPlace" class="form-control" name="item[lastSeenPlace]" value="{/document/item/lastSeenPlace}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Дата пропажи</label>
            <div class="col-xs-7">
                <div class="input-group date" id="eventDate">
                    <input class="form-control" type="text" name="item[eventDate]" value="{/document/item/eventDate}"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Время пропажи</label>
            <div class="col-xs-7">
                <div class="input-group clockpicker" data-autoclose="true">
                    <input type="text" class="form-control" name="item[eventTime]" value="{/document/item/eventTime}" id="eventTime"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
                </div>
            </div>
        </div>
    </div>
    <xsl:call-template name="formHR"/>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Порода</label>
            <div class="col-xs-7">
                <input type="text" id="breed" class="form-control" name="item[breed]" value="{/document/item/breed}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Окрас</label>
            <div class="col-xs-7">
                <input type="text" id="color" class="form-control" name="item[color]" value="{/document/item/color}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Глаза</label>
            <div class="col-xs-7">
                <input type="text" id="eyes" class="form-control" name="item[eyes]" value="{/document/item/eyes}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Уши</label>
            <div class="col-xs-7">
                <input type="text" id="ears" class="form-control" name="item[ears]" value="{/document/item/ears}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Хвост</label>
            <div class="col-xs-7">
                <input type="text" id="tail" class="form-control" name="item[tail]" value="{/document/item/tail}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Возраст</label>
            <div class="col-xs-7">
                <input type="text" id="age" class="form-control" name="item[age]" value="{/document/item/age}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Особые приметы</label>
            <div class="col-xs-7">
                <input type="text" id="specialSigns" class="form-control" name="item[specialSigns]" value="{/document/item/specialSigns}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Обстоятельства пропажи</label>
            <div class="col-xs-7">
                <textarea id="text" class="form-control" name="item[text]" style="width: 100%;" rows="5"><xsl:value-of select="/document/item/text" disable-output-escaping="yes"/></textarea>
            </div>
        </div>
    </div>
    <xsl:call-template name="formHR"/>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Телефон</label>
            <div class="col-xs-7">
                <input type="text" id="phone" class="form-control" name="item[phone]" value="{/document/item/phone}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <!--<div class="form-group">
        <div class="row">
            <label class="control-label col-xs-3">Контактное лицо</label>
            <div class="col-xs-7">
                <input type="text" id="contacterName" class="form-control" name="item[contacterName]" value="{/document/item/contacterName}" style="width: 100%;"/>
            </div>
        </div>
    </div>-->
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5">Вознагражение</label>
            <div class="col-xs-7">
                <input type="text" id="reward" class="form-control" name="item[reward]" value="{/document/item/reward}" style="width: 100%;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
        <label class="col-xs-5 control-label">Фотография</label>
            <div class="col-xs-7">
                <div class="input-group">
                    <label class="input-group-btn">
                        <span class="btn btn-default">
                            Обзор… <input type="file" name="itemImages[]" style="display: none;" multiple="false"/>
                        </span>
                    </label>
                    <input id="fileName" type="text" class="form-control" readonly="1"/>
                </div>
            </div>
        </div>
    </div>
    <xsl:if test="/document/action != 'new'">
        <div class="form-group">
            <div class="row">
                <label class="col-xs-5 control-label">Загруженные фото</label>
                <div class="col-xs-7">
                    <xsl:call-template name="imagesForm"/>
                </div>
            </div>
        </div>
    </xsl:if>
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-5"></label>
            <div class="col-xs-7">
                <xsl:variable name="btnCaption">
                    <xsl:choose>
                        <xsl:when test="$edit">Сохранить</xsl:when>
                        <xsl:otherwise>Разместить</xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <input type="submit" class="btn btn-primary" value="{$btnCaption}"/>
                <xsl:if test="$edit">
                    &#160;<a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/delete/{/document/item/id}" class="btn btn-default" onclick="return deleteRecord({/document/item/id});">
                        Удалить объявление
                    </a>
                </xsl:if>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template name="formHR">
    <div class="form-group">
        <div class="row">
            <xsl:choose>
                <xsl:when test="$MOBILE = '1'">
                    <div class="col-xs-12">
                        <hr class="formHR"/>
                    </div>
                </xsl:when>
                <xsl:otherwise>
                    <div class="col-xs-3"/>
                    <div class="col-xs-7">
                        <hr class="formHR"/>
                    </div>
                </xsl:otherwise>
            </xsl:choose>            
        </div>
    </div>
</xsl:template>

<xsl:template name="advertsListBlocked">
    <xsl:param name="adverts" select="/document/items/item"/>
    <xsl:choose>
        <xsl:when test="count($adverts/id) > 0">
            <xsl:for-each select="$adverts">
                <div class="row">
                    <div class="col-xs-12 advertBlock">
                        <xsl:choose>
                            <xsl:when test="count(images/image/id)>0">
                                <div class="advertsListPreviewImage">
                                    <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/view/{id}">
                                        <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="images/image[1]/path"/></xsl:variable>
                                        <img height="140" width="140" src="/{$currentImagePath}&amp;h=140&amp;w=140&amp;p=1" alt="{images/image[1]/description}" title="{images/image[1]/description}"/>
                                    </a>
                                </div>
                            </xsl:when>
                            <xsl:otherwise>
                                <!-- no image -->
                            </xsl:otherwise>
                        </xsl:choose>
                        <div>
                            <xsl:variable name="id" select="id"/>
                            <xsl:variable name="categoryId" select="categoryId"/>
                            <xsl:variable name="name">
                                <xsl:choose>
                                    <xsl:when test="name != ''"><xsl:value-of select="name"/></xsl:when>
                                    <xsl:when test="(name = '') and (categoryId = 0)"></xsl:when>
                                    <xsl:otherwise><!--Пропал(а): -->
                                        <xsl:variable name="category" select="/document/categories/category[id=$categoryId]"/>
                                        <xsl:choose>
                                            <xsl:when test="(gender = 'Male') and ($category/nameMale != '')"><xsl:value-of select="$category/nameMale"/></xsl:when>
                                            <xsl:when test="(gender = 'Female') and ($category/nameFemale != '')"><xsl:value-of select="$category/nameFemale"/></xsl:when>
                                            <xsl:otherwise><xsl:value-of select="$category/name"/></xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:if test="nickname != ''">&#160;<xsl:value-of select="nickname"/></xsl:if>
                            </xsl:variable>
                            <span class="advertName">
                                <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/view/{id}">
                                    <xsl:value-of select="$name"/>
                                </a>
                                <xsl:if test="(count(/document/userAdverts/item[id=$id]) > 0) or ($userType = $USER_TYPE_ADMIN)">
                                    &#160;
                                    <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/edit/{id}" data-toggle="tooltip" data-placement="top" data-original-title="Редактировать">
                                        <span class="glyphicon glyphicon-edit advertActionIcon"/>
                                        <!--<span class="editButton">Редактировать</span>-->
                                    </a>
                                </xsl:if>
                            </span>
                            <span class="advertPlace">
                                <xsl:value-of select="city"/><xsl:if test="(city != '') and (lastSeenPlace != '')">, </xsl:if>
                                <xsl:value-of select="lastSeenPlace"/>
                            </span>
                            <span class="advertPreviewText"><xsl:value-of select="previewText"/></span>
                            <xsl:if test="(eventDate != '') and (eventDate != '0000-00-00 00:00:00')"><span class="advertEventDate"><!--Дата: --><xsl:value-of select="eventDate"/></span></xsl:if>
                            <!--<span class="advertActions">
                                <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/print/{id}" target="_blank" data-toggle="tooltip" data-placement="left" data-original-title="Распечатать">
                                    <span class="glyphicon glyphicon-print"/>
                                </a>
                            </span>-->
                        </div>
                    </div>
                </div>
            </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
            <div class="row">
                <div class="col-xs-12 text-center text-muted">
                    Объявлений не найдено!
                </div>
            </div>
        </xsl:otherwise>
    </xsl:choose>
    <div class="row">
        <xsl:call-template name="navigation"/>
    </div>
</xsl:template>

<xsl:template name="viewAdverts">
    <h1>Пропали животные</h1>
    <xsl:call-template name="advertsListBlocked"/>
</xsl:template>

<xsl:template name="myAdverts">
    <h1>Мои объявления</h1>
    <xsl:call-template name="advertsListBlocked"/>
</xsl:template>

<xsl:template name="viewAdvert">
    <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="/document/item/images/image[1]/path"/></xsl:variable>
    <h1>Пропал друг!</h1>
    <div class="row advertCard">
        <xsl:choose>
            <xsl:when test="$MOBILE='1'">
                <div class="col-xs-12">
                    <a href="/{$currentImagePath}" data-lightbox="item">
                        <img class="blogPreviewImage" width="100%" src="/{$currentImagePath}&amp;h=1280&amp;w=1280&amp;p=1" alt="{/document/item/images/image[1]/description}" title="{/document/item/images/image[1]/description}" style="float: left; padding-bottom: 14px;"/>
                    </a>
                    <xsl:call-template name="advertContent"/>
                </div>
            </xsl:when>
            <xsl:otherwise>
                <div class="col-xs-6" align="center">
                    <a href="/{$currentImagePath}" data-lightbox="item">
                        <img class="blogPreviewImage" width="100%" src="/{$currentImagePath}&amp;h=1280&amp;w=1280&amp;p=1" alt="{/document/item/images/image[1]/description}" title="{/document/item/images/image[1]/description}"/>
                    </a>
                </div>
                <div class="col-xs-6">
                    <xsl:call-template name="advertContent"/>
                </div>
            </xsl:otherwise>
        </xsl:choose>
    </div>
</xsl:template>

<xsl:template name="advertName">
    <xsl:variable name="categoryId" select="/document/item/categoryId"/>
    <xsl:variable name="name">
        <xsl:choose>
            <xsl:when test="/document/item/name != ''"><xsl:value-of select="/document/item/name"/></xsl:when>
            <xsl:when test="(/document/item/name = '') and (/document/item/categoryId = 0)"></xsl:when>
            <xsl:otherwise><!--Пропал(а): -->
                <xsl:variable name="category" select="/document/categories/category[id=$categoryId]"/>
                <xsl:choose>
                    <xsl:when test="(/document/item/gender = 'Male') and ($category/nameMale != '')"><xsl:value-of select="$category/nameMale"/></xsl:when>
                    <xsl:when test="(/document/item/gender = 'Female') and ($category/nameFemale != '')"><xsl:value-of select="$category/nameFemale"/></xsl:when>
                    <xsl:otherwise><xsl:value-of select="$category/name"/></xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="/document/item/nickname != ''">&#160;<xsl:value-of select="/document/item/nickname"/></xsl:if>
    </xsl:variable>
    <xsl:value-of select="$name"/>
</xsl:template>

<xsl:template name="advertContent">
    <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="/document/item/images/image[1]/path"/></xsl:variable>
    <xsl:variable name="id" select="/document/item/id"/>
    <xsl:variable name="name"><xsl:call-template name="advertName"/></xsl:variable>
    <span class="advertName"><xsl:value-of select="$name"/></span>
    <xsl:if test="(count(/document/userAdverts/item[id=$id]) > 0) or ($userType = $USER_TYPE_ADMIN)">
        &#160;
        <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/edit/{/document/item/id}" data-toggle="tooltip" data-placement="top" data-original-title="Редактировать">
            <span class="glyphicon glyphicon-edit advertActionIcon"/>
        </a>
    </xsl:if>
    <br/>
    <xsl:variable name="place"><xsl:value-of select="/document/item/city"/><xsl:if test="(/document/item/city != '') and (/document/item/lastSeenPlace != '')">, </xsl:if><xsl:value-of select="/document/item/lastSeenPlace"/></xsl:variable>
    <xsl:if test="$place != ''">
        <span class="advertParam">
            <span class="name">Место: </span><span class="value"><xsl:value-of select="$place"/></span>
            &#160;
            <a name="map_{id}" href="https://maps.google.com/?q={$place}" target="_blank" onclick="/*showMap('{$place}')*/ return true;">
                <span class="glyphicon glyphicon-search" data-toggle="tooltip" data-placement="top" data-original-title="Искать на карте"/><!--glyphicon-map-marker-->
            </a>
        </span>
    </xsl:if>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Окрас'"/><xsl:with-param name="value" select="/document/item/color"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Глаза'"/><xsl:with-param name="value" select="/document/item/eyes"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Уши'"/><xsl:with-param name="value" select="/document/item/ears"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Хвост'"/><xsl:with-param name="value" select="/document/item/tail"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Порода'"/><xsl:with-param name="value" select="/document/item/breed"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Возраст'"/><xsl:with-param name="value" select="/document/item/age"/></xsl:call-template>
    <xsl:variable name="gender"><xsl:choose><xsl:when test="/document/item/gender = 'Male'">Мужской</xsl:when><xsl:when test="/document/item/gender = 'Female'">Женский</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Пол'"/><xsl:with-param name="value" select="$gender"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Особые приметы'"/><xsl:with-param name="value" select="/document/item/specialSigns"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Обстоятельства пропажи'"/><xsl:with-param name="value" select="/document/item/text"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Дата пропажи'"/><xsl:with-param name="value" select="/document/item/eventDate"/></xsl:call-template>
    <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Вознаграждение'"/><xsl:with-param name="value" select="/document/item/reward"/></xsl:call-template>

    <xsl:if test="/document/item/phone != ''">
        <span class="advertParam">
            <span class="name">Телефон для связи: </span>
            <xsl:choose>
                <xsl:when test="$MOBILE='1'">
                    <span class="value">
                        <a class="btn btn-success" href="tel:{/document/item/phone}"><xsl:value-of select="/document/item/phone"/></a>
                    </span>
                </xsl:when>
                <xsl:otherwise>
                    <span class="value phone">
                        <xsl:value-of select="/document/item/phone"/>
                    </span>
                </xsl:otherwise>
            </xsl:choose>
        </span>
    </xsl:if>

    <br/>
    <br/>
    <div>
        <xsl:if test="$MOBILE='1'">
            <xsl:attribute name="align">center</xsl:attribute>
        </xsl:if>
        <a href="/{$siteRoot}{/document/dictionaryInfo/moduleName}/print/{/document/item/id}" target="_blank">
            <button class="btn btn-primary">Распечатать</button>
        </a>
        <br/>
        <br/>
        <span class="share">
            Поделиться: 
            <xsl:variable name="caption"><xsl:choose><xsl:when test="/document/item/gender = 'Male'">Потерялся</xsl:when><xsl:when test="/document/item/gender = 'Female'">Потерялась</xsl:when><xsl:otherwise>Потерялось животное -</xsl:otherwise></xsl:choose></xsl:variable>

            <script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
            <script src="//yastatic.net/share2/share.js"></script>
            <xsl:variable name="services">vkontakte,facebook,odnoklassniki,twitter<xsl:if test="$MOBILE = '1'">,whatsapp</xsl:if></xsl:variable>
            <div class="ya-share2" 
                data-title="{$caption} {$name}"
                data-image="{$currentImagePath}"
                data-services="{$services}">
            </div>
        </span>        
    </div>
</xsl:template>

<xsl:template name="advertCardParam">
    <xsl:param name="name"/>
    <xsl:param name="value"/>
    <xsl:param name="class" seect="''"/>
    <xsl:if test="$value != ''">
        <span class="advertParam {$class}">
            <span class="name"><xsl:value-of select="$name"/>: </span><span class="value"><xsl:value-of select="$value" disable-output-escaping="yes"/></span>
        </span>
    </xsl:if>
</xsl:template>

<xsl:template name="defaultOut">
    <!--<div style="padding-top:12px;" align="left">
        <xsl:call-template name="readableData">
            <xsl:with-param name="node" select="/"/>
        </xsl:call-template>
    </div>-->
    <xsl:choose>
        <xsl:when test="/document/action = 'new'">
            <xsl:call-template name="newAdvertForm"/>
        </xsl:when>
        <xsl:when test="/document/action = 'edit'">
            <xsl:call-template name="newAdvertForm"/>
        </xsl:when>
        <xsl:when test="/document/action = 'view'">
            <xsl:call-template name="viewAdvert"/>
        </xsl:when>
        <xsl:when test="/document/action = 'myAdverts'">
            <xsl:call-template name="myAdverts"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="viewAdverts"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="images">
    <xsl:if test="count(/document/item/images/image/id)>0">
        <h2>Фотографии</h2>
        <div class="row">
            <div class="col-sm-12">
                <xsl:for-each select="/document/item/images/image">
                    <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="path"/></xsl:variable>
                    <div class="imagePreview" align="center">
                        <a href="/{$currentImagePath}" data-lightbox="blogItem">
                            <img class="blogPreviewImage" src="/{$currentImagePath}&amp;h=160&amp;w=160&amp;p=1" alt="{description}" title="{description}"/>
                        </a>
                    </div>
                </xsl:for-each>
            </div>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template name="imagesForm">
    <xsl:if test="count(/document/item/images/image/id)>0">
        <xsl:for-each select="/document/item/images/image">
            <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="path"/></xsl:variable>
            <div class="imageControl" align="center">
                <a href="/{$currentImagePath}" data-lightbox="advertImage">
                    <img height="120" width="120" src="/{$currentImagePath}&amp;h=120&amp;w=120&amp;p=1" alt="{description}" title="{description}"/>
                </a>
                <xsl:if test="name != ''">
                    <div class="name"><strong><xsl:value-of select="name"/></strong></div>
                </xsl:if>
                <!--<div class="addedDatetime">Добавлена <xsl:value-of select="addedDatetime"/></div>-->
                <xsl:if test="description != ''">
                    <div class="description"><xsl:value-of select="description"/></div>
                </xsl:if>
                <div>
                    <a href="?action=deleteImage&amp;id={id}" title="Удалить фото" onclick="return deleteImage({id}, {/document/item/id});">
                        <small><span class="glyphicon glyphicon-remove"></span> Удалить</small>
                    </a>
                </div>
            </div>
        </xsl:for-each>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>