<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    <xsl:param name="add_paragraph_numbers" select="0"/>
    
    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- elements to leave out -->
    <xsl:template match="html|body|head">
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!-- put content and comments beneth each other -->
    <xsl:template match="div[@class='xlas-block']">
        <xsl:variable name="counter" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::initCurrentComments', string(@data-p))" />
        <div class="xlas-block">
            <div class="xlas-comments-left">
                <xsl:apply-templates select="node()" />
            </div>
            <div class="xlas-comments-right">
                <xsl:for-each select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::getCurrentComments')/node()">
                    <xsl:copy-of select="." />
                </xsl:for-each>
            </div>
        </div>
    </xsl:template>
    
    <!-- add the marking and label for comments -->
    <xsl:template match="span">
        <xsl:choose>
            <xsl:when test="@data-w">
                <xsl:variable name="label" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::commentLabel',string(@data-w))" />
                <xsl:variable name="color" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::commentColor',string(@data-w))" />
                <xsl:if test="$label">
                    <sup class="xlas-label">
                        <xsl:value-of select="$label" />
                    </sup>
                </xsl:if>
                <xsl:choose>
                    <xsl:when test="$color">
                        <span>
                            <xsl:attribute name="style">background-color: <xsl:value-of select="$color" />;</xsl:attribute>
                            <xsl:value-of select="text()" />
                        </span>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:copy-of select="text()" />
                    </xsl:otherwise>
                </xsl:choose>
             </xsl:when>
            <xsl:otherwise>
                <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
