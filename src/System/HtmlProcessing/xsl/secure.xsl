<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    
    <!-- default rule: copy nothing -->
    <xsl:template match="*|@*">
    </xsl:template>

    <!-- process children of html and body -->
    <xsl:template match="html|body">
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!-- copy and process allowed elements -->
    <xsl:template match="blockquote|br|code|em|h1|h2|h3|h4|h5|h6|hr|li|ol|p|pre|span|strong|s|sub|sup|table|tbody|td|th|thead|tr|u|ul">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()" />
        </xsl:copy>
    </xsl:template>

    <!-- copy allowed attributes -->
    <xsl:template match="@border|@colspan|@rowspan">
        <xsl:copy></xsl:copy>
    </xsl:template>

    <!-- restrict style settings -->
    <xsl:template match="@style">
        <xsl:attribute name="style">
            <xsl:value-of select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::filterStyle', string(.))" />
        </xsl:attribute>
    </xsl:template>

</xsl:stylesheet>