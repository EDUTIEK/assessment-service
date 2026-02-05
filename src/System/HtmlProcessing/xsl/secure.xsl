<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    
    <!--  Basic rule: copy nothing -->
    <xsl:template match="*|@*">
    </xsl:template>

    <xsl:template match="html|body">
        <xsl:apply-templates select="*" />
    </xsl:template>

    <!-- copy only allowed elements -->
    <xsl:template match="h1|h2|h3|h4|h5|h6|p|ul|ol|li|p|pre|strong|span|sub|sup|em|u|table|thead|tbody|th|tr|td|hr|br">
        <xsl:copy>
            <xsl:apply-templates select="@*" />
            <xsl:apply-templates select="node()" />
        </xsl:copy>
    </xsl:template>

    <!-- todo: pagebreaks are xomments -->

    <!-- copy allowed attributes -->
    <xsl:template match="@style">
        <xsl:copy></xsl:copy>
    </xsl:template>

</xsl:stylesheet>