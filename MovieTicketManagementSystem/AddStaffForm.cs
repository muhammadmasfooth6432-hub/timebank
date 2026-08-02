using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Data;
using System.Data.SqlClient;

namespace MovieTicketManagementSystem
{
    public partial class AddStaffForm : UserControl
    {
        //Global Variable
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";      // Connection String for SQL Server
        public AddStaffForm()     // Constructor
        {
            InitializeComponent();
            dispilayData();      //load staff list into grid
        }


        /// <summary>
        /// safely update the UI(like DataGridView)
        /// </summary>
        public void refreshData()
        {
            if (InvokeRequired)
            {
                Invoke((MethodInvoker)refreshData);  // Use Invoke() to make sure the ui update runs on the main UI thred
                return;
            }
            dispilayData();
        }

        /// <summary>
        /// load staff list
        /// </summary>
        public void dispilayData()
        {
            staffData sData = new staffData();

            List<staffData> listData = sData.staffdataListData();  //form the class
            dataGridView1.DataSource = listData;
        }


        /// <summary>
        /// Add staff
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void buttonAddStaff_Click(object sender, EventArgs e)
        {
            if (isEmpty())
            {
                MessageBox.Show("Empty fields","Error Message",MessageBoxButtons.OK,MessageBoxIcon.Error) ;
            }
            else
            {
                using(SqlConnection connect=new SqlConnection(conn))     // connects to sql server
                {
                    connect.Open();

                    string selectUsername = "SELECT*FROM users WHERE username=@usern";     

                    using (SqlCommand checkUsern = new SqlCommand(selectUsername, connect))
                    {
                        checkUsern.Parameters.AddWithValue("@usern", textBoxUnameStaff.Text.Trim());

                        SqlDataAdapter adapter = new SqlDataAdapter(checkUsern);
                        DataTable table = new DataTable();

                        adapter.Fill(table);

                        if (table.Rows.Count > 0)
                        {
                            MessageBox.Show(textBoxUnameStaff.Text.Substring(0, 1).ToUpper()
                                + textBoxUnameStaff.Text.Substring(1) + "is Exiting Already", "Error Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
                        }
                        else
                        {
                            string insertData = "INSERT INTO users (username,password,role,status,date_reg)" +
                               "VALUES(@usern,@pass,@role,@status,@date)";

                            using(SqlCommand cmd = new SqlCommand(insertData, connect))
                            {
                                cmd.Parameters.AddWithValue("@usern", textBoxUnameStaff.Text.Trim());
                                cmd.Parameters.AddWithValue("@pass", textBoxPassStaff.Text.Trim());
                                cmd.Parameters.AddWithValue("@role", "staff");
                                cmd.Parameters.AddWithValue("@status",comboBoxStatusStf.SelectedItem.ToString());

                                DateTime today = DateTime.Today;

                                cmd.Parameters.AddWithValue("@date", today);

                                cmd.ExecuteNonQuery();

                                MessageBox.Show("Added Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);

                                clearFields();
                                dispilayData();
                            }

                        }
                    }
                }
            }
        }

        /// <summary>
        /// checking  all the fields are empty
        /// </summary>
        /// <returns></returns>
        public bool isEmpty()
        {
            if (textBoxUnameStaff.Text == "" || textBoxPassStaff.Text == "" || comboBoxStatusStf.SelectedIndex == -1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }


        /// <summary>
        /// select staff from grid
        /// </summary>
        private int getID = 0;
        private void dataGridView1_CellClick(object sender, DataGridViewCellEventArgs e)
        {
            if(e.RowIndex != -1)
            {
                DataGridViewRow row = dataGridView1.Rows[e.RowIndex];

                getID=(int)row.Cells[0].Value;
                textBoxUnameStaff.Text = row.Cells[1].Value.ToString();
                textBoxPassStaff.Text = row.Cells[2].Value.ToString();
                comboBoxStatusStf.SelectedItem = row.Cells[4].Value.ToString();
            }
        }
        /// <summary>
        /// update staff info
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void buttonUpdateStaff_Click(object sender, EventArgs e)
        {
            if (isEmpty())
            {
                MessageBox.Show("Empty Fields", "ERROE Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            else
            {

                if(DialogResult.Yes==MessageBox.Show("Are You Sure Want To Update Id:"+getID+"?","Confirmation Message", MessageBoxButtons.YesNo, MessageBoxIcon.Question))
                {
                    using (SqlConnection connect = new SqlConnection(conn))
                    {
                        connect.Open();

                        string selectUsername = "SELECT * FROM users WHERE username=@usern";

                        using (SqlCommand checkUsern = new SqlCommand(selectUsername, connect))
                        {
                            checkUsern.Parameters.AddWithValue("@usern", textBoxUnameStaff.Text.Trim());

                            SqlDataAdapter adapter = new SqlDataAdapter(checkUsern);
                            DataTable table = new DataTable();

                            adapter.Fill(table);

                            if (table.Rows.Count >= 2)
                            {

                                MessageBox.Show(textBoxUnameStaff.Text.Substring(0, 1).ToUpper()
                                    + textBoxUnameStaff.Text.Substring(1) + "is Exiting Already", "Error Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
                            }
                            else
                            {
                                string updatedata = "UPDATE users SET username=@usern,password=@pass,status=@status WHERE id=@id";

                                using (SqlCommand cmd = new SqlCommand(updatedata, connect))
                                {
                                    cmd.Parameters.AddWithValue("@usern", textBoxUnameStaff.Text.Trim());
                                    cmd.Parameters.AddWithValue("@pass", textBoxPassStaff.Text.Trim());
                                    cmd.Parameters.AddWithValue("@status", comboBoxStatusStf.SelectedItem.ToString());
                                    cmd.Parameters.AddWithValue("@id", getID);

                                    cmd.ExecuteNonQuery();

                                    MessageBox.Show("Update Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);

                                    clearFields();
                                    dispilayData();
                                }
                            }
                        }

                    }
                }
                
            }
        }


        /// <summary>
        /// clear the fields are staffName,password,staff status(clear form fields)
        /// </summary>
        public void clearFields()
        {
            textBoxUnameStaff.Text = "";
            textBoxPassStaff.Text = "";
            comboBoxStatusStf.SelectedItem = -1;
        }


        /// <summary>
        /// delete staff (soft delete)
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void buttonDeleteStaff_Click(object sender, EventArgs e)
        {
            if (isEmpty())
            {
                MessageBox.Show("Empty Fields", "ERROE Message", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            else
            {

                if (DialogResult.Yes == MessageBox.Show("Are You Sure Want To Delete Id:" + getID + "?", "Confirmation Message", MessageBoxButtons.YesNo, MessageBoxIcon.Question))
                {
                    using (SqlConnection connect = new SqlConnection(conn))
                    {
                        connect.Open();

                        string updatedata = "UPDATE users SET status=@status WHERE id=@id";

                        using (SqlCommand cmd = new SqlCommand(updatedata, connect))
                        {
                            cmd.Parameters.AddWithValue("@status", "Deleted");
                            cmd.Parameters.AddWithValue("@id", getID);

                            cmd.ExecuteNonQuery();

                            MessageBox.Show("Deleted Successful", "Information Message", MessageBoxButtons.OK, MessageBoxIcon.Information);

                            clearFields();
                            dispilayData();
                        }
                    }
                }
            }
        }

        /// <summary>
        /// clear button
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void buttonClearStaff_Click(object sender, EventArgs e)
        {
            clearFields();
        }
    }
}
