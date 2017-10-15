<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>
<xsl:variable name="imgPath"><xsl:value-of select="$modulesPath"/>xslt_page_builder/img/</xsl:variable>
<xsl:variable name="commonImgPath"><xsl:value-of select="$modulesPath"/>common/img/</xsl:variable>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <div class="row">
        <div class="col-lg-12">
            <h1>О проекте</h1>
            <p>
                Проект &quot;Оперативный поиск потерянных питомцев&quot; создан для помощи хозяевам в быстром распространении информации о пропаже и для упрощения процедуры поиска.
            </p>
            <p>
                Минималистичный и легковесный сервис состоит из списка пропавших животных и конструктора для быстрого составления объявлений о пропаже. 
                На объявлениях автоматически генерируется QR-код, с помощью которого можно попасть на сайт Опердога за подробностями и связаться с хозяином.
            </p>
            <p>
                Сайт оптимизирован для мобильных устройств, позволяет загружает изображения питомцев и указывать их последнее известное местонахождение.
            </p>
            <p>
                Автор идеи Оксана Большакова, над проектом работали программист Григорий Можаровский и волонтеры проекта &quot;Оперативный поиск потерянных питомцев&quot;. Впервые идея сервиса была представлена на хакатоне Social Startup Hackathon по разработке социальных приложений, который проводился Теплицей социальных технологий во Владивостоке.
            </p>
            <br/>
            <h2>Вы можете</h2>
            <ul>
                <li>добавить свое объявление в базу поиска;</li>
                <li>быстро оформить ориентировку (листовку) для распространения информации в бумажном виде.</li>
            </ul>
            <br/>
            <p>
                Также код сервиса &quot;Опердог&quot; доступен в открытом виде на GitHub: <a href="https://github.com/gydrus/operdog.git">https://github.com/gydrus/operdog.git</a>.
            </p>
            <br/>
            <h2>Контактная информация</h2>
            <p>
                Телефон: 8 914 325-08-07<br/>
                E-mail: <span class="email">finn-fox[a@t]mail.ru</span>
            </p>
        </div>
    </div>
</xsl:template>

</xsl:stylesheet>