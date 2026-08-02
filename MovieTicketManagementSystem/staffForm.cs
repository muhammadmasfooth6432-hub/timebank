using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace MovieTicketManagementSystem
{
    public partial class staffForm : Form
    {
        public staffForm()
        {
            InitializeComponent();
        }

        private void btnAclose_Click(object sender, EventArgs e)
        {
            if (DialogResult.Yes == MessageBox.Show("Are You Sure You Want to Exit", "Confirmation Message", MessageBoxButtons.YesNo, MessageBoxIcon.Question))
            {
                Application.Exit();
            }
        }

        private void logout_btn_Click(object sender, EventArgs e)
        {
            if (DialogResult.Yes == MessageBox.Show("Are You Sure You Want to logout", "Confirmation Message", MessageBoxButtons.YesNo, MessageBoxIcon.Question))
            {
                Form1 loginForm = new Form1();
                loginForm.Show();

                this.Hide();
            }
        }

        private void dashbord_btn_Click(object sender, EventArgs e)
        {
            dasboardForm1.Show();
            buyTicketForm1.Hide();

            dasboardForm dForm = dasboardForm1 as dasboardForm;

            if (dForm != null)
            {
                dForm.refreshData();
            }
        }

        private void buttonBuyTicket_Click(object sender, EventArgs e)
        {

            dasboardForm1.Hide();
            buyTicketForm1.Show();

            buyTicketForm btForm = buyTicketForm1 as buyTicketForm;

            if (btForm != null)
            {
                btForm.refreshData();
            }
        }
    }
}
