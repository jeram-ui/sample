Public Class frmImmunazation
    Public _type, _busID, _ppID As String
    Private Sub frmImmunazation_Load(sender As Object, e As EventArgs) Handles MyBase.Load
        Load_data()
    End Sub

    Private Sub btnRefresh_Click(sender As Object, e As EventArgs) Handles btnRefresh.Click
        Load_data()
    End Sub

    Sub Load_data()
        DataGridPhysical.DataSource = DataSource("Call cvl_get_immunazation_history('" & _type & "','" & _busID & "','" & _ppID & "','" & Date.Now.Year & "')")
    End Sub

    Private Sub DataGridPhysical_DataSourceChanged(sender As Object, e As EventArgs) Handles DataGridPhysical.DataSourceChanged
        Try
            With DataGridPhysical
                .ExtendRightColumn = True
                For x As Integer = 0 To .Columns.Count - 1
                    Select Case .Columns(x).Caption

                        Case "Reference No"
                            .Splits(0).DisplayColumns(x).Width = 150
                            .Splits(0).DisplayColumns(x).HeadingStyle.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center
                            .Splits(0).DisplayColumns(x).Style.VerticalAlignment = C1.Win.C1TrueDBGrid.AlignVertEnum.Center
                            .Splits(0).DisplayColumns(x).Style.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center
                        Case "Date of Exam"
                            .Splits(0).DisplayColumns(x).Width = 150
                            .Splits(0).DisplayColumns(x).HeadingStyle.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center
                            .Splits(0).DisplayColumns(x).Style.VerticalAlignment = C1.Win.C1TrueDBGrid.AlignVertEnum.Center
                            .Splits(0).DisplayColumns(x).Style.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center

                        Case "Result"
                            .Splits(0).DisplayColumns(x).Width = 180
                            .Splits(0).DisplayColumns(x).HeadingStyle.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center
                            .Splits(0).DisplayColumns(x).Style.VerticalAlignment = C1.Win.C1TrueDBGrid.AlignVertEnum.Center
                            .Splits(0).DisplayColumns(x).Style.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center

                        Case "Place"
                            .Splits(0).DisplayColumns(x).Width = 240
                            .Splits(0).DisplayColumns(x).HeadingStyle.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center
                            .Splits(0).DisplayColumns(x).Style.VerticalAlignment = C1.Win.C1TrueDBGrid.AlignVertEnum.Center
                            .Splits(0).DisplayColumns(x).Style.HorizontalAlignment = C1.Win.C1TrueDBGrid.AlignHorzEnum.Center


                        Case Else
                            .Splits(0).DisplayColumns(x).Visible = False
                            .Splits(0).DisplayColumns(x).AllowSizing = False
                    End Select
                Next

            End With
        Catch ex As Exception

        End Try
    End Sub
End Class