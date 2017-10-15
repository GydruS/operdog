<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../../modules/common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <!--
    <div style="padding-top:12px;" align="left">
        <xsl:call-template name="readableData">
            <xsl:with-param name="node" select="/"/>
        </xsl:call-template>
    </div>
    -->
    
    <xsl:choose>
        <!-- Если модуль вызван из модуля manage и админом -->
        <xsl:when test="count(/document/context/loadedModules/module[name='manage'])>0">
            <xsl:if test="/document/context/user/type = $USER_TYPE_ADMIN">
                <div>
                    <xsl:choose>
                        <xsl:when test="/document/action='create'">
                            <xsl:call-template name="itemForm"/>
                        </xsl:when>
                        <xsl:when test="/document/action='edit'">
                            <xsl:call-template name="itemForm"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:call-template name="itemsTable"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <xsl:value-of select="Content"/>
            </xsl:if>
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="defaultOut"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="itemsTable">
    <table cellspacing="0" border="0" cellpadding="0" class="dataTable">
        <tr class="tableHeader">
            <td nowrap="nowrap">Name</td>
            <td nowrap="nowrap">Action</td>
        </tr>
        <xsl:choose>
            <xsl:when test="count(/document/items/item)>0">
                <xsl:for-each select="/document/items/item">
                    <tr>
                        <td nowrap="nowrap"><xsl:value-of select="name"/></td>
                        <td>
                            <a href="?edit_item={id}">edit</a>
                            &#160;
                            <a href="?delete_item={id}">delete</a>
                        </td>
                    </tr>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <tr>
                    <td colspan="2" align="center">
                        <i>No records</i>
                    </td>
                </tr>
            </xsl:otherwise>
        </xsl:choose>
    </table>
    <xsl:if test="$userType = $USER_TYPE_ADMIN">
        <br/>
<!--        <a href="/{$siteRoot}manage/{/document/dictionaryInfo/moduleName}/create">-->
        <a href="?create">
            <img src="{$imgPath}icons/plus.gif" align="absmiddle" class="icon"/>Add new
        </a>
    </xsl:if>
</xsl:template>

<xsl:template name="itemForm">
    <xsl:choose>
        <xsl:when test="count(/document/item/id)>0">
            <h3>Edit item</h3>
        </xsl:when>
        <xsl:otherwise>
            <h3>Add item</h3>
        </xsl:otherwise>
    </xsl:choose>
    
<!--    <form action="/{$siteRoot}manage/{/document/dictionaryInfo/moduleName}/" method="post" enctype="multipart/form-data" accept-charset="utf-8">-->
<!--    <xsl:variable name="formAction"><xsl:for-each select="/document/context/request/queriedStruct"><xsl:value-of select="."/>/</xsl:for-each></xsl:variable>
    <form action="/{$siteRoot}{$formAction}" method="post" enctype="multipart/form-data" accept-charset="utf-8">-->
    <form action="/{$siteRoot}{$pageRelativeQuriedPath}" method="post" enctype="multipart/form-data" accept-charset="utf-8">
        <input type="hidden" name="item[id]" value="{/document/item/id}"/>
        <table border="0">
            <tr>
                <td>Name:</td>
                <td><input type="text" name="item[name]" value="{/document/item/name}"/></td>
            </tr>
            <tr>
                <td>&#160;</td>
                <td><input type="submit" class="button" value="Save"/></td>
            </tr>
        </table>
    </form>
</xsl:template>

<xsl:template name="defaultOut">
</xsl:template>

</xsl:stylesheet>