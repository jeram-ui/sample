<Global.Microsoft.VisualBasic.CompilerServices.DesignerGenerated()> _
Partial Class frmImmunazation
    Inherits System.Windows.Forms.Form

    'Form overrides dispose to clean up the component list.
    <System.Diagnostics.DebuggerNonUserCode()> _
    Protected Overrides Sub Dispose(ByVal disposing As Boolean)
        Try
            If disposing AndAlso components IsNot Nothing Then
                components.Dispose()
            End If
        Finally
            MyBase.Dispose(disposing)
        End Try
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    <System.Diagnostics.DebuggerStepThrough()> _
    Private Sub InitializeComponent()
        Dim resources As System.ComponentModel.ComponentResourceManager = New System.ComponentModel.ComponentResourceManager(GetType(frmImmunazation))
        Me.Panel5 = New System.Windows.Forms.Panel()
        Me.Label40 = New System.Windows.Forms.Label()
        Me.FlowLayoutPanel2 = New System.Windows.Forms.FlowLayoutPanel()
        Me.Panel10 = New System.Windows.Forms.Panel()
        Me.ButtonX7 = New DevComponents.DotNetBar.ButtonX()
        Me.Panel11 = New System.Windows.Forms.Panel()
        Me.TabControl2 = New DevComponents.DotNetBar.SuperTabControl()
        Me.SuperTabControlPanel7 = New DevComponents.DotNetBar.SuperTabControlPanel()
        Me.Label19 = New System.Windows.Forms.Label()
        Me.GroupBox1 = New System.Windows.Forms.GroupBox()
        Me.DataGridPhysical = New C1.Win.C1TrueDBGrid.C1TrueDBGrid()
        Me.TabItem1 = New DevComponents.DotNetBar.SuperTabItem()
        Me.SuperTabControlPanel6 = New DevComponents.DotNetBar.SuperTabControlPanel()
        Me.SuperTabControlPanel1 = New DevComponents.DotNetBar.SuperTabControlPanel()
        Me.grid_display_lists = New C1.Win.C1TrueDBGrid.C1TrueDBGrid()
        Me.Panel7 = New System.Windows.Forms.Panel()
        Me.FlowLayoutPanel1 = New System.Windows.Forms.FlowLayoutPanel()
        Me.Label39 = New System.Windows.Forms.Label()
        Me.cmb_slectperiod = New System.Windows.Forms.ComboBox()
        Me.txt_datefrom = New System.Windows.Forms.DateTimePicker()
        Me.txt_dateto = New System.Windows.Forms.DateTimePicker()
        Me.Panel4 = New System.Windows.Forms.Panel()
        Me.Label11 = New System.Windows.Forms.Label()
        Me.lblTotal = New System.Windows.Forms.Label()
        Me.TabItem2 = New DevComponents.DotNetBar.SuperTabItem()
        Me.btnRefresh = New DevComponents.DotNetBar.ButtonX()
        Me.Panel5.SuspendLayout()
        Me.FlowLayoutPanel2.SuspendLayout()
        CType(Me.TabControl2, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.TabControl2.SuspendLayout()
        Me.SuperTabControlPanel7.SuspendLayout()
        Me.GroupBox1.SuspendLayout()
        CType(Me.DataGridPhysical, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.SuperTabControlPanel6.SuspendLayout()
        Me.SuperTabControlPanel1.SuspendLayout()
        CType(Me.grid_display_lists, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.Panel7.SuspendLayout()
        Me.FlowLayoutPanel1.SuspendLayout()
        Me.Panel4.SuspendLayout()
        Me.SuspendLayout()
        '
        'Panel5
        '
        Me.Panel5.BackColor = System.Drawing.Color.White
        Me.Panel5.Controls.Add(Me.Label40)
        Me.Panel5.Controls.Add(Me.FlowLayoutPanel2)
        Me.Panel5.Dock = System.Windows.Forms.DockStyle.Top
        Me.Panel5.Location = New System.Drawing.Point(0, 0)
        Me.Panel5.Name = "Panel5"
        Me.Panel5.Size = New System.Drawing.Size(852, 65)
        Me.Panel5.TabIndex = 759
        '
        'Label40
        '
        Me.Label40.AutoSize = True
        Me.Label40.Font = New System.Drawing.Font("Century Gothic", 18.0!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label40.Location = New System.Drawing.Point(4, 11)
        Me.Label40.Name = "Label40"
        Me.Label40.Size = New System.Drawing.Size(89, 30)
        Me.Label40.TabIndex = 751
        Me.Label40.Text = "History"
        '
        'FlowLayoutPanel2
        '
        Me.FlowLayoutPanel2.Controls.Add(Me.Panel10)
        Me.FlowLayoutPanel2.Controls.Add(Me.btnRefresh)
        Me.FlowLayoutPanel2.Controls.Add(Me.ButtonX7)
        Me.FlowLayoutPanel2.Controls.Add(Me.Panel11)
        Me.FlowLayoutPanel2.Dock = System.Windows.Forms.DockStyle.Right
        Me.FlowLayoutPanel2.Location = New System.Drawing.Point(697, 0)
        Me.FlowLayoutPanel2.Name = "FlowLayoutPanel2"
        Me.FlowLayoutPanel2.RightToLeft = System.Windows.Forms.RightToLeft.Yes
        Me.FlowLayoutPanel2.Size = New System.Drawing.Size(155, 65)
        Me.FlowLayoutPanel2.TabIndex = 768
        '
        'Panel10
        '
        Me.Panel10.Location = New System.Drawing.Point(-306, 3)
        Me.Panel10.Name = "Panel10"
        Me.Panel10.Size = New System.Drawing.Size(458, 3)
        Me.Panel10.TabIndex = 766
        '
        'ButtonX7
        '
        Me.ButtonX7.AccessibleRole = System.Windows.Forms.AccessibleRole.PushButton
        Me.ButtonX7.Anchor = CType((System.Windows.Forms.AnchorStyles.Top Or System.Windows.Forms.AnchorStyles.Right), System.Windows.Forms.AnchorStyles)
        Me.ButtonX7.Font = New System.Drawing.Font("Century", 9.75!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.ButtonX7.Location = New System.Drawing.Point(62, 12)
        Me.ButtonX7.Name = "ButtonX7"
        Me.ButtonX7.Shape = New DevComponents.DotNetBar.EllipticalShapeDescriptor()
        Me.ButtonX7.Size = New System.Drawing.Size(42, 40)
        Me.ButtonX7.Symbol = ""
        Me.ButtonX7.TabIndex = 768
        Me.ButtonX7.Tooltip = "Print List"
        Me.ButtonX7.Visible = False
        '
        'Panel11
        '
        Me.Panel11.Location = New System.Drawing.Point(49, 12)
        Me.Panel11.Name = "Panel11"
        Me.Panel11.Size = New System.Drawing.Size(7, 45)
        Me.Panel11.TabIndex = 766
        '
        'TabControl2
        '
        Me.TabControl2.BackColor = System.Drawing.Color.White
        '
        '
        '
        '
        '
        '
        Me.TabControl2.ControlBox.CloseBox.Name = ""
        '
        '
        '
        Me.TabControl2.ControlBox.MenuBox.Name = ""
        Me.TabControl2.ControlBox.Name = ""
        Me.TabControl2.ControlBox.SubItems.AddRange(New DevComponents.DotNetBar.BaseItem() {Me.TabControl2.ControlBox.MenuBox, Me.TabControl2.ControlBox.CloseBox})
        Me.TabControl2.Controls.Add(Me.SuperTabControlPanel7)
        Me.TabControl2.Controls.Add(Me.SuperTabControlPanel6)
        Me.TabControl2.Dock = System.Windows.Forms.DockStyle.Fill
        Me.TabControl2.ForeColor = System.Drawing.Color.Black
        Me.TabControl2.Location = New System.Drawing.Point(0, 65)
        Me.TabControl2.Name = "TabControl2"
        Me.TabControl2.Padding = New System.Windows.Forms.Padding(10)
        Me.TabControl2.ReorderTabsEnabled = True
        Me.TabControl2.SelectedTabFont = New System.Drawing.Font("Century Gothic", 8.25!, System.Drawing.FontStyle.Bold)
        Me.TabControl2.SelectedTabIndex = 1
        Me.TabControl2.Size = New System.Drawing.Size(852, 478)
        Me.TabControl2.TabFont = New System.Drawing.Font("Century Gothic", 8.25!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.TabControl2.TabIndex = 761
        Me.TabControl2.Tabs.AddRange(New DevComponents.DotNetBar.BaseItem() {Me.TabItem1, Me.TabItem2})
        Me.TabControl2.TabStyle = DevComponents.DotNetBar.eSuperTabStyle.Office2010BackstageBlue
        Me.TabControl2.Text = "TRANSACTION TAB"
        '
        'SuperTabControlPanel7
        '
        Me.SuperTabControlPanel7.AutoScroll = True
        Me.SuperTabControlPanel7.Controls.Add(Me.Label19)
        Me.SuperTabControlPanel7.Controls.Add(Me.GroupBox1)
        Me.SuperTabControlPanel7.Dock = System.Windows.Forms.DockStyle.Fill
        Me.SuperTabControlPanel7.Location = New System.Drawing.Point(0, 24)
        Me.SuperTabControlPanel7.Name = "SuperTabControlPanel7"
        Me.SuperTabControlPanel7.Size = New System.Drawing.Size(852, 454)
        Me.SuperTabControlPanel7.TabIndex = 0
        Me.SuperTabControlPanel7.TabItem = Me.TabItem1
        Me.SuperTabControlPanel7.ThemeAware = True
        '
        'Label19
        '
        Me.Label19.Anchor = System.Windows.Forms.AnchorStyles.Top
        Me.Label19.AutoSize = True
        Me.Label19.BackColor = System.Drawing.Color.Transparent
        Me.Label19.Font = New System.Drawing.Font("Century Gothic", 9.75!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label19.Location = New System.Drawing.Point(760, 427)
        Me.Label19.Name = "Label19"
        Me.Label19.Size = New System.Drawing.Size(0, 17)
        Me.Label19.TabIndex = 1190
        '
        'GroupBox1
        '
        Me.GroupBox1.Anchor = System.Windows.Forms.AnchorStyles.Top
        Me.GroupBox1.BackColor = System.Drawing.Color.Transparent
        Me.GroupBox1.Controls.Add(Me.DataGridPhysical)
        Me.GroupBox1.Location = New System.Drawing.Point(47, 22)
        Me.GroupBox1.Name = "GroupBox1"
        Me.GroupBox1.Size = New System.Drawing.Size(774, 403)
        Me.GroupBox1.TabIndex = 190
        Me.GroupBox1.TabStop = False
        '
        'DataGridPhysical
        '
        Me.DataGridPhysical.AccessibleName = "cto_official_reciept_setup"
        Me.DataGridPhysical.AllowSort = False
        Me.DataGridPhysical.AllowUpdate = False
        Me.DataGridPhysical.BackColor = System.Drawing.Color.White
        Me.DataGridPhysical.CaptionHeight = 25
        Me.DataGridPhysical.ExtendRightColumn = True
        Me.DataGridPhysical.FetchRowStyles = True
        Me.DataGridPhysical.FilterBar = True
        Me.DataGridPhysical.Font = New System.Drawing.Font("Century Gothic", 9.75!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.DataGridPhysical.Images.Add(CType(resources.GetObject("DataGridPhysical.Images"), System.Drawing.Image))
        Me.DataGridPhysical.Location = New System.Drawing.Point(18, 22)
        Me.DataGridPhysical.MarqueeStyle = C1.Win.C1TrueDBGrid.MarqueeEnum.HighlightRow
        Me.DataGridPhysical.Name = "DataGridPhysical"
        Me.DataGridPhysical.PreviewInfo.Location = New System.Drawing.Point(0, 0)
        Me.DataGridPhysical.PreviewInfo.Size = New System.Drawing.Size(0, 0)
        Me.DataGridPhysical.PreviewInfo.ZoomFactor = 75.0R
        Me.DataGridPhysical.PrintInfo.PageSettings = CType(resources.GetObject("DataGridPhysical.PrintInfo.PageSettings"), System.Drawing.Printing.PageSettings)
        Me.DataGridPhysical.RecordSelectors = False
        Me.DataGridPhysical.RowHeight = 20
        Me.DataGridPhysical.Size = New System.Drawing.Size(737, 360)
        Me.DataGridPhysical.TabIndex = 1222
        Me.DataGridPhysical.Text = "C1TrueDBGrid1"
        Me.DataGridPhysical.VisualStyle = C1.Win.C1TrueDBGrid.VisualStyle.Office2007Blue
        Me.DataGridPhysical.PropBag = resources.GetString("DataGridPhysical.PropBag")
        '
        'TabItem1
        '
        Me.TabItem1.AttachedControl = Me.SuperTabControlPanel7
        Me.TabItem1.GlobalItem = False
        Me.TabItem1.Name = "TabItem1"
        Me.TabItem1.Text = "Comulative Data"
        '
        'SuperTabControlPanel6
        '
        Me.SuperTabControlPanel6.Controls.Add(Me.SuperTabControlPanel1)
        Me.SuperTabControlPanel6.Controls.Add(Me.Panel4)
        Me.SuperTabControlPanel6.Dock = System.Windows.Forms.DockStyle.Fill
        Me.SuperTabControlPanel6.Location = New System.Drawing.Point(0, 24)
        Me.SuperTabControlPanel6.Name = "SuperTabControlPanel6"
        Me.SuperTabControlPanel6.Size = New System.Drawing.Size(782, 333)
        Me.SuperTabControlPanel6.TabIndex = 0
        Me.SuperTabControlPanel6.TabItem = Me.TabItem2
        '
        'SuperTabControlPanel1
        '
        Me.SuperTabControlPanel1.Controls.Add(Me.grid_display_lists)
        Me.SuperTabControlPanel1.Controls.Add(Me.Panel7)
        Me.SuperTabControlPanel1.Dock = System.Windows.Forms.DockStyle.Fill
        Me.SuperTabControlPanel1.Location = New System.Drawing.Point(0, 0)
        Me.SuperTabControlPanel1.Name = "SuperTabControlPanel1"
        Me.SuperTabControlPanel1.Size = New System.Drawing.Size(782, 303)
        Me.SuperTabControlPanel1.TabIndex = 1055
        '
        'grid_display_lists
        '
        Me.grid_display_lists.AccessibleName = "cto_official_reciept_setup"
        Me.grid_display_lists.AllowSort = False
        Me.grid_display_lists.AllowUpdate = False
        Me.grid_display_lists.BackColor = System.Drawing.Color.White
        Me.grid_display_lists.CaptionHeight = 25
        Me.grid_display_lists.Dock = System.Windows.Forms.DockStyle.Fill
        Me.grid_display_lists.ExtendRightColumn = True
        Me.grid_display_lists.FetchRowStyles = True
        Me.grid_display_lists.FilterBar = True
        Me.grid_display_lists.Font = New System.Drawing.Font("Century Gothic", 9.75!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.grid_display_lists.Images.Add(CType(resources.GetObject("grid_display_lists.Images"), System.Drawing.Image))
        Me.grid_display_lists.Location = New System.Drawing.Point(0, 59)
        Me.grid_display_lists.MarqueeStyle = C1.Win.C1TrueDBGrid.MarqueeEnum.HighlightRow
        Me.grid_display_lists.Name = "grid_display_lists"
        Me.grid_display_lists.PreviewInfo.Location = New System.Drawing.Point(0, 0)
        Me.grid_display_lists.PreviewInfo.Size = New System.Drawing.Size(0, 0)
        Me.grid_display_lists.PreviewInfo.ZoomFactor = 75.0R
        Me.grid_display_lists.PrintInfo.PageSettings = CType(resources.GetObject("grid_display_lists.PrintInfo.PageSettings"), System.Drawing.Printing.PageSettings)
        Me.grid_display_lists.RecordSelectors = False
        Me.grid_display_lists.RowHeight = 20
        Me.grid_display_lists.Size = New System.Drawing.Size(782, 244)
        Me.grid_display_lists.TabIndex = 1131
        Me.grid_display_lists.Text = "C1TrueDBGrid1"
        Me.grid_display_lists.VisualStyle = C1.Win.C1TrueDBGrid.VisualStyle.Office2007Blue
        Me.grid_display_lists.PropBag = resources.GetString("grid_display_lists.PropBag")
        '
        'Panel7
        '
        Me.Panel7.Controls.Add(Me.FlowLayoutPanel1)
        Me.Panel7.Dock = System.Windows.Forms.DockStyle.Top
        Me.Panel7.Location = New System.Drawing.Point(0, 0)
        Me.Panel7.Name = "Panel7"
        Me.Panel7.Size = New System.Drawing.Size(782, 59)
        Me.Panel7.TabIndex = 1052
        '
        'FlowLayoutPanel1
        '
        Me.FlowLayoutPanel1.BackColor = System.Drawing.Color.Transparent
        Me.FlowLayoutPanel1.Controls.Add(Me.Label39)
        Me.FlowLayoutPanel1.Controls.Add(Me.cmb_slectperiod)
        Me.FlowLayoutPanel1.Controls.Add(Me.txt_datefrom)
        Me.FlowLayoutPanel1.Controls.Add(Me.txt_dateto)
        Me.FlowLayoutPanel1.Location = New System.Drawing.Point(2, 15)
        Me.FlowLayoutPanel1.Name = "FlowLayoutPanel1"
        Me.FlowLayoutPanel1.Size = New System.Drawing.Size(585, 28)
        Me.FlowLayoutPanel1.TabIndex = 756
        '
        'Label39
        '
        Me.Label39.Font = New System.Drawing.Font("Tahoma", 9.0!, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label39.Location = New System.Drawing.Point(3, 0)
        Me.Label39.Name = "Label39"
        Me.Label39.Size = New System.Drawing.Size(79, 20)
        Me.Label39.TabIndex = 1104
        Me.Label39.Text = "Select Date :"
        Me.Label39.TextAlign = System.Drawing.ContentAlignment.MiddleLeft
        '
        'cmb_slectperiod
        '
        Me.cmb_slectperiod.AutoCompleteMode = System.Windows.Forms.AutoCompleteMode.SuggestAppend
        Me.cmb_slectperiod.AutoCompleteSource = System.Windows.Forms.AutoCompleteSource.ListItems
        Me.cmb_slectperiod.Font = New System.Drawing.Font("Tahoma", 9.0!)
        Me.cmb_slectperiod.FormattingEnabled = True
        Me.cmb_slectperiod.Items.AddRange(New Object() {"Select Range", "Daily", "Month", "Year"})
        Me.cmb_slectperiod.Location = New System.Drawing.Point(88, 3)
        Me.cmb_slectperiod.Name = "cmb_slectperiod"
        Me.cmb_slectperiod.Size = New System.Drawing.Size(149, 22)
        Me.cmb_slectperiod.TabIndex = 1105
        '
        'txt_datefrom
        '
        Me.txt_datefrom.CustomFormat = "MMMM yyyy"
        Me.txt_datefrom.Font = New System.Drawing.Font("Tahoma", 9.0!)
        Me.txt_datefrom.Format = System.Windows.Forms.DateTimePickerFormat.Custom
        Me.txt_datefrom.Location = New System.Drawing.Point(243, 3)
        Me.txt_datefrom.Name = "txt_datefrom"
        Me.txt_datefrom.Size = New System.Drawing.Size(152, 22)
        Me.txt_datefrom.TabIndex = 1106
        '
        'txt_dateto
        '
        Me.txt_dateto.CustomFormat = "MMMM yyyy"
        Me.txt_dateto.Font = New System.Drawing.Font("Tahoma", 9.0!)
        Me.txt_dateto.Format = System.Windows.Forms.DateTimePickerFormat.Custom
        Me.txt_dateto.Location = New System.Drawing.Point(401, 3)
        Me.txt_dateto.Name = "txt_dateto"
        Me.txt_dateto.Size = New System.Drawing.Size(164, 22)
        Me.txt_dateto.TabIndex = 1107
        '
        'Panel4
        '
        Me.Panel4.Controls.Add(Me.Label11)
        Me.Panel4.Controls.Add(Me.lblTotal)
        Me.Panel4.Dock = System.Windows.Forms.DockStyle.Bottom
        Me.Panel4.Location = New System.Drawing.Point(0, 303)
        Me.Panel4.Name = "Panel4"
        Me.Panel4.Size = New System.Drawing.Size(782, 30)
        Me.Panel4.TabIndex = 1054
        '
        'Label11
        '
        Me.Label11.AutoSize = True
        Me.Label11.BackColor = System.Drawing.Color.Transparent
        Me.Label11.Font = New System.Drawing.Font("Microsoft Sans Serif", 8.25!)
        Me.Label11.ForeColor = System.Drawing.Color.Black
        Me.Label11.Location = New System.Drawing.Point(-2, 9)
        Me.Label11.Name = "Label11"
        Me.Label11.Size = New System.Drawing.Size(106, 13)
        Me.Label11.TabIndex = 171
        Me.Label11.Text = "TOTAL RECORD/S:"
        '
        'lblTotal
        '
        Me.lblTotal.AutoSize = True
        Me.lblTotal.BackColor = System.Drawing.Color.Transparent
        Me.lblTotal.Font = New System.Drawing.Font("Microsoft Sans Serif", 8.25!)
        Me.lblTotal.ForeColor = System.Drawing.Color.Black
        Me.lblTotal.Location = New System.Drawing.Point(117, 9)
        Me.lblTotal.Name = "lblTotal"
        Me.lblTotal.Size = New System.Drawing.Size(13, 13)
        Me.lblTotal.TabIndex = 170
        Me.lblTotal.Text = "0"
        '
        'TabItem2
        '
        Me.TabItem2.AttachedControl = Me.SuperTabControlPanel6
        Me.TabItem2.GlobalItem = False
        Me.TabItem2.Name = "TabItem2"
        Me.TabItem2.Text = "Cumulative Data"
        Me.TabItem2.Visible = False
        '
        'btnRefresh
        '
        Me.btnRefresh.AccessibleRole = System.Windows.Forms.AccessibleRole.PushButton
        Me.btnRefresh.Anchor = CType((System.Windows.Forms.AnchorStyles.Top Or System.Windows.Forms.AnchorStyles.Right), System.Windows.Forms.AnchorStyles)
        Me.btnRefresh.Font = New System.Drawing.Font("Century", 9.75!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.btnRefresh.Location = New System.Drawing.Point(110, 12)
        Me.btnRefresh.Name = "btnRefresh"
        Me.btnRefresh.Shape = New DevComponents.DotNetBar.EllipticalShapeDescriptor()
        Me.btnRefresh.Size = New System.Drawing.Size(42, 40)
        Me.btnRefresh.Symbol = ""
        Me.btnRefresh.TabIndex = 771
        Me.btnRefresh.Tooltip = "Unclassified Accounts"
        '
        'frmImmunazation
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(6.0!, 13.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(852, 543)
        Me.Controls.Add(Me.TabControl2)
        Me.Controls.Add(Me.Panel5)
        Me.Name = "frmImmunazation"
        Me.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen
        Me.Text = "History"
        Me.Panel5.ResumeLayout(False)
        Me.Panel5.PerformLayout()
        Me.FlowLayoutPanel2.ResumeLayout(False)
        CType(Me.TabControl2, System.ComponentModel.ISupportInitialize).EndInit()
        Me.TabControl2.ResumeLayout(False)
        Me.SuperTabControlPanel7.ResumeLayout(False)
        Me.SuperTabControlPanel7.PerformLayout()
        Me.GroupBox1.ResumeLayout(False)
        CType(Me.DataGridPhysical, System.ComponentModel.ISupportInitialize).EndInit()
        Me.SuperTabControlPanel6.ResumeLayout(False)
        Me.SuperTabControlPanel1.ResumeLayout(False)
        CType(Me.grid_display_lists, System.ComponentModel.ISupportInitialize).EndInit()
        Me.Panel7.ResumeLayout(False)
        Me.FlowLayoutPanel1.ResumeLayout(False)
        Me.Panel4.ResumeLayout(False)
        Me.Panel4.PerformLayout()
        Me.ResumeLayout(False)

    End Sub

    Friend WithEvents Panel5 As Panel
    Friend WithEvents Label40 As Label
    Friend WithEvents FlowLayoutPanel2 As FlowLayoutPanel
    Friend WithEvents Panel10 As Panel
    Friend WithEvents ButtonX7 As DevComponents.DotNetBar.ButtonX
    Friend WithEvents Panel11 As Panel
    Friend WithEvents TabControl2 As DevComponents.DotNetBar.SuperTabControl
    Friend WithEvents SuperTabControlPanel7 As DevComponents.DotNetBar.SuperTabControlPanel
    Friend WithEvents Label19 As Label
    Friend WithEvents GroupBox1 As GroupBox
    Private WithEvents DataGridPhysical As C1.Win.C1TrueDBGrid.C1TrueDBGrid
    Friend WithEvents TabItem1 As DevComponents.DotNetBar.SuperTabItem
    Friend WithEvents SuperTabControlPanel6 As DevComponents.DotNetBar.SuperTabControlPanel
    Friend WithEvents SuperTabControlPanel1 As DevComponents.DotNetBar.SuperTabControlPanel
    Private WithEvents grid_display_lists As C1.Win.C1TrueDBGrid.C1TrueDBGrid
    Friend WithEvents Panel7 As Panel
    Friend WithEvents FlowLayoutPanel1 As FlowLayoutPanel
    Friend WithEvents Label39 As Label
    Friend WithEvents cmb_slectperiod As ComboBox
    Friend WithEvents txt_datefrom As DateTimePicker
    Friend WithEvents txt_dateto As DateTimePicker
    Friend WithEvents Panel4 As Panel
    Friend WithEvents Label11 As Label
    Friend WithEvents lblTotal As Label
    Friend WithEvents TabItem2 As DevComponents.DotNetBar.SuperTabItem
    Friend WithEvents btnRefresh As DevComponents.DotNetBar.ButtonX
End Class
